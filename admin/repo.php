<?php
/**
 * ProjectRepo, thin abstraction over MySQL (PDO) or MongoDB.
 * Automatically picks MongoDB when MONGODB_URI env var is set,
 * otherwise falls back to MySQL via getPDO().
 */

require_once __DIR__ . '/config.php';

class ProjectRepo {

    private string $driver;
    /** @var \MongoDB\Collection|null */
    private $col = null;
    private ?PDO $pdo = null;

    public function __construct() {
        $mongoUri = getenv('MONGODB_URI');
        if ($mongoUri && class_exists('\MongoDB\Client')) {
            try {
                $client    = new \MongoDB\Client($mongoUri);
                $this->col = $client->iportfolio_db->projects;
                $this->driver = 'mongo';
                return;
            } catch (\Exception $e) {
                error_log('MongoDB connection failed: ' . $e->getMessage());
            }
        }
        // Fall back to MySQL
        require_once __DIR__ . '/db.php';
        $this->pdo    = getPDO();
        $this->driver = 'mysql';
    }

    public function isMongo(): bool { return $this->driver === 'mongo'; }
    public function driver(): string { return $this->driver; }

    // ─── Stats (counts across ALL rows, not just current page) ────────────────

    public function stats(): array {
        if ($this->isMongo()) {
            $total   = (int)$this->col->countDocuments();
            $visible = (int)$this->col->countDocuments(['is_visible' => true]);
            $cats    = count($this->col->distinct('category'));
            return ['total' => $total, 'visible' => $visible, 'cats' => $cats];
        }
        return $this->pdo->query(
            "SELECT COUNT(*) AS total, SUM(is_visible) AS visible, COUNT(DISTINCT category) AS cats FROM projects"
        )->fetch();
    }

    public function totalCount(): int {
        if ($this->isMongo()) return (int)$this->col->countDocuments();
        return (int)$this->pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
    }

    // ─── Read ─────────────────────────────────────────────────────────────────

    public function findPage(int $limit, int $offset): array {
        if ($this->isMongo()) {
            $cursor = $this->col->find([], [
                'sort'    => ['sort_order' => 1, '_id' => 1],
                'limit'   => $limit,
                'skip'    => $offset,
                'typeMap' => ['root' => 'array', 'document' => 'array'],
            ]);
            return $this->hydrateMongo($cursor);
        }
        return $this->pdo->query(
            "SELECT * FROM projects ORDER BY sort_order ASC, id ASC LIMIT $limit OFFSET $offset"
        )->fetchAll();
    }

    public function findVisible(): array {
        if ($this->isMongo()) {
            $cursor = $this->col->find(['is_visible' => true], [
                'sort'    => ['sort_order' => 1, '_id' => 1],
                'typeMap' => ['root' => 'array', 'document' => 'array'],
            ]);
            return $this->hydrateMongo($cursor);
        }
        return $this->pdo->query(
            "SELECT * FROM projects WHERE is_visible = 1 ORDER BY sort_order ASC, id ASC"
        )->fetchAll();
    }

    public function getImage(string $id): ?string {
        if ($this->isMongo()) {
            $doc = $this->col->findOne(
                ['_id' => new \MongoDB\BSON\ObjectId($id)],
                ['typeMap' => ['root' => 'array']]
            );
            return $doc['image'] ?? null;
        }
        $row = $this->pdo->query("SELECT image FROM projects WHERE id = " . (int)$id)->fetch();
        return $row['image'] ?? null;
    }

    // ─── Write ────────────────────────────────────────────────────────────────

    public function insert(array $data): void {
        if ($this->isMongo()) {
            $data['is_visible'] = (bool)($data['is_visible'] ?? false);
            $data['sort_order'] = (int)($data['sort_order']  ?? 0);
            $data['created_at'] = new \MongoDB\BSON\UTCDateTime();
            $this->col->insertOne($data);
            return;
        }
        $this->pdo->prepare("
            INSERT INTO projects (title, category, description, image, live_url, github_url, tags, is_visible, sort_order)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ")->execute([
            $data['title'], $data['category'], $data['description'],
            $data['image'], $data['live_url'], $data['github_url'],
            $data['tags'], $data['is_visible'] ? 1 : 0, $data['sort_order'],
        ]);
    }

    public function findById(string $id): ?array {
        if ($this->isMongo()) {
            $doc = $this->col->findOne(
                ['_id' => new \MongoDB\BSON\ObjectId($id)],
                ['typeMap' => ['root' => 'array', 'document' => 'array']]
            );
            if (!$doc) return null;
            $doc['id'] = (string)$doc['_id'];
            return $doc;
        }
        $st = $this->pdo->prepare("SELECT * FROM projects WHERE id = ?");
        $st->execute([(int)$id]);
        return $st->fetch() ?: null;
    }

    public function update(string $id, array $data): void {
        if ($this->isMongo()) {
            $set = [
                'title'       => $data['title'],
                'category'    => $data['category'],
                'description' => $data['description'],
                'live_url'    => $data['live_url'],
                'github_url'  => $data['github_url'],
                'tags'        => $data['tags'],
                'is_visible'  => (bool)$data['is_visible'],
                'sort_order'  => (int)$data['sort_order'],
            ];
            if (array_key_exists('image', $data)) $set['image'] = $data['image'];
            $this->col->updateOne(
                ['_id' => new \MongoDB\BSON\ObjectId($id)],
                ['$set' => $set]
            );
            return;
        }
        if (array_key_exists('image', $data)) {
            $this->pdo->prepare("
                UPDATE projects
                SET title=?,category=?,description=?,image=?,live_url=?,github_url=?,tags=?,is_visible=?,sort_order=?
                WHERE id=?
            ")->execute([
                $data['title'],$data['category'],$data['description'],$data['image'],
                $data['live_url'],$data['github_url'],$data['tags'],
                $data['is_visible']?1:0,(int)$data['sort_order'],(int)$id
            ]);
        } else {
            $this->pdo->prepare("
                UPDATE projects
                SET title=?,category=?,description=?,live_url=?,github_url=?,tags=?,is_visible=?,sort_order=?
                WHERE id=?
            ")->execute([
                $data['title'],$data['category'],$data['description'],
                $data['live_url'],$data['github_url'],$data['tags'],
                $data['is_visible']?1:0,(int)$data['sort_order'],(int)$id
            ]);
        }
    }

    public function delete(string $id): void {
        if ($this->isMongo()) {
            $this->col->deleteOne(['_id' => new \MongoDB\BSON\ObjectId($id)]);
            return;
        }
        $this->pdo->prepare("DELETE FROM projects WHERE id = ?")->execute([(int)$id]);
    }

    public function toggle(string $id): void {
        if ($this->isMongo()) {
            $doc = $this->col->findOne(
                ['_id' => new \MongoDB\BSON\ObjectId($id)],
                ['typeMap' => ['root' => 'array']]
            );
            $current = $doc['is_visible'] ?? true;
            $this->col->updateOne(
                ['_id' => new \MongoDB\BSON\ObjectId($id)],
                ['$set' => ['is_visible' => !$current]]
            );
            return;
        }
        $this->pdo->prepare("UPDATE projects SET is_visible = 1 - is_visible WHERE id = ?")->execute([(int)$id]);
    }

    public function reorder(string $id, int $pos): void {
        if ($this->isMongo()) {
            $this->col->updateOne(
                ['_id' => new \MongoDB\BSON\ObjectId($id)],
                ['$set' => ['sort_order' => $pos]]
            );
            return;
        }
        $this->pdo->prepare("UPDATE projects SET sort_order = ? WHERE id = ?")->execute([$pos, (int)$id]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function hydrateMongo(iterable $cursor): array {
        $results = [];
        foreach ($cursor as $doc) {
            $doc['id'] = (string)$doc['_id'];
            $results[] = $doc;
        }
        return $results;
    }
}
