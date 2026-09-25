<?php
class User {
    public static function findByEmail(string $email): ?array {
        return Database::row('SELECT * FROM users WHERE email = ?', [$email]);
    }
    public static function findById(int $id): ?array {
        return Database::row('SELECT * FROM users WHERE id = ?', [$id]);
    }
    public static function findByGoogleId(string $gid): ?array {
        return Database::row('SELECT * FROM users WHERE google_id = ?', [$gid]);
    }
    public static function create(array $d): int {
        Database::query(
            'INSERT INTO users (name,email,password,role,google_id,avatar,is_verified) VALUES (?,?,?,?,?,?,?)',
            [$d['name'],$d['email'],$d['password']??null,$d['role']??'user',$d['google_id']??null,$d['avatar']??null,(int)($d['is_verified']??false)]
        );
        return (int) Database::lastInsertId();
    }
    public static function update(int $id, array $data): void {
        $sets=[]; $vals=[];
        foreach($data as $k=>$v){ $sets[]="$k=?"; $vals[]= is_bool($v) ? (int)$v : $v; }
        $vals[]=$id;
        Database::query('UPDATE users SET '.implode(',',$sets).",updated_at=NOW() WHERE id=?",$vals);
    }
    public static function all(): array {
        return Database::rows('SELECT id,name,email,role,is_verified,created_at FROM users ORDER BY created_at DESC');
    }
    public static function delete(int $id): void { Database::query('DELETE FROM users WHERE id=?',[$id]); }
    public static function count(): int { return (int)Database::row('SELECT COUNT(*) as c FROM users')['c']; }
}
