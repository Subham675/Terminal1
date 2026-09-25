<?php
class MenuItem {
    public static function all(): array {
        return Database::rows('SELECT m.*,c.name as category_name FROM menu_items m LEFT JOIN menu_categories c ON m.category_id=c.id ORDER BY c.sort_order,m.sort_order,m.name');
    }
    public static function byCategory(): array {
        $grouped=[];
        foreach(self::all() as $item) $grouped[$item['category_name']][]=$item;
        return $grouped;
    }
    public static function categories(): array {
        return Database::rows('SELECT * FROM menu_categories ORDER BY sort_order');
    }
    public static function findById(int $id): ?array {
        return Database::row('SELECT * FROM menu_items WHERE id=?',[$id]);
    }
    public static function create(array $d): void {
        Database::query(
            'INSERT INTO menu_items (category_id,name,description,price,badge,is_available,is_veg,image_url,sort_order) VALUES (?,?,?,?,?,?,?,?,?)',
            [$d['category_id']??null,$d['name'],$d['description']??null,(float)$d['price'],$d['badge']??null,isset($d['is_available'])?1:0,isset($d['is_veg'])?1:0,$d['image_url']??null,(int)($d['sort_order']??0)]
        );
    }
    public static function update(int $id, array $d): void {
        Database::query(
            'UPDATE menu_items SET category_id=?,name=?,description=?,price=?,badge=?,is_available=?,is_veg=?,image_url=?,sort_order=?,updated_at=NOW() WHERE id=?',
            [$d['category_id']??null,$d['name'],$d['description']??null,(float)$d['price'],$d['badge']??null,isset($d['is_available'])?1:0,isset($d['is_veg'])?1:0,$d['image_url']??null,(int)($d['sort_order']??0),$id]
        );
    }
    public static function delete(int $id): void { Database::query('DELETE FROM menu_items WHERE id=?',[$id]); }
    public static function count(): int { return (int)Database::row('SELECT COUNT(*) as c FROM menu_items')['c']; }
}
