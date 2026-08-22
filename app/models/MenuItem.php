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
        return Database::row('SELECT * FROM menu_items WHERE id=$1',[$id]);
    }
    public static function create(array $d): void {
        Database::query(
            'INSERT INTO menu_items (category_id,name,description,price,badge,is_available,is_veg,image_url,sort_order) VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9)',
            [$d['category_id']??null,$d['name'],$d['description']??null,(float)$d['price'],$d['badge']??null,isset($d['is_available'])?'true':'false',isset($d['is_veg'])?'true':'false',$d['image_url']??null,(int)($d['sort_order']??0)]
        );
    }
    public static function update(int $id, array $d): void {
        Database::query(
            'UPDATE menu_items SET category_id=$1,name=$2,description=$3,price=$4,badge=$5,is_available=$6,is_veg=$7,image_url=$8,sort_order=$9,updated_at=NOW() WHERE id=$10',
            [$d['category_id']??null,$d['name'],$d['description']??null,(float)$d['price'],$d['badge']??null,isset($d['is_available'])?'true':'false',isset($d['is_veg'])?'true':'false',$d['image_url']??null,(int)($d['sort_order']??0),$id]
        );
    }
    public static function delete(int $id): void { Database::query('DELETE FROM menu_items WHERE id=$1',[$id]); }
    public static function count(): int { return (int)Database::row('SELECT COUNT(*) as c FROM menu_items')['c']; }
}
