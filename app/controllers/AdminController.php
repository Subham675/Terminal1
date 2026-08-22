<?php
class AdminController {
    public static function dashboard(): void {
        requireLogin(); requireAdmin();
        $stats = [
            'users'     => User::count(),
            'bookings'  => Booking::count(),
            'pending'   => Booking::countByStatus('pending'),
            'confirmed' => Booking::countByStatus('confirmed'),
            'menu_items'=> MenuItem::count(),
        ];
        $recent_bookings = Booking::recent(8);
        require APP_ROOT.'/app/views/admin/dashboard.php';
    }

    // ── BOOKINGS ──────────────────────────────────────────
    public static function bookings(): void {
        requireLogin(); requireAdmin();
        $bookings = Booking::all();
        require APP_ROOT.'/app/views/admin/bookings.php';
    }
    public static function updateBookingStatus(): void {
        requireLogin(); requireAdmin(); verifyCsrf();
        $id     = (int)($_POST['id'] ?? 0);
        $status = sanitize($_POST['status'] ?? '');
        if($id && in_array($status,['pending','confirmed','cancelled','completed'])){
            Booking::updateStatus($id,$status);
            flash('bookings','Booking status updated.','success');
        }
        redirect('/admin/bookings');
    }
    public static function deleteBooking(): void {
        requireLogin(); requireAdmin(); verifyCsrf();
        $id = (int)($_POST['id'] ?? 0);
        if($id){ Booking::delete($id); flash('bookings','Booking deleted.','success'); }
        redirect('/admin/bookings');
    }

    // ── USERS ─────────────────────────────────────────────
    public static function users(): void {
        requireLogin(); requireAdmin();
        $users = User::all();
        require APP_ROOT.'/app/views/admin/users.php';
    }
    public static function updateUserRole(): void {
        requireLogin(); requireAdmin(); verifyCsrf();
        $id   = (int)($_POST['id'] ?? 0);
        $role = sanitize($_POST['role'] ?? '');
        if($id && in_array($role,['admin','user'])){ User::update($id,['role'=>$role]); flash('users','Role updated.','success'); }
        redirect('/admin/users');
    }
    public static function deleteUser(): void {
        requireLogin(); requireAdmin(); verifyCsrf();
        $id = (int)($_POST['id'] ?? 0);
        if($id && $id !== (int)authUser()['id']){ User::delete($id); flash('users','User deleted.','success'); }
        redirect('/admin/users');
    }

    // ── MENU ──────────────────────────────────────────────
    public static function menu(): void {
        requireLogin(); requireAdmin();
        $items      = MenuItem::all();
        $categories = MenuItem::categories();
        require APP_ROOT.'/app/views/admin/menu.php';
    }
    public static function createMenuItem(): void {
        requireLogin(); requireAdmin(); verifyCsrf();
        MenuItem::create([
            'category_id' => (int)($_POST['category_id']??0),
            'name'        => sanitize($_POST['name']??''),
            'description' => sanitize($_POST['description']??''),
            'price'       => $_POST['price']??0,
            'badge'       => sanitize($_POST['badge']??''),
            'is_available'=> isset($_POST['is_available']),
            'is_veg'      => isset($_POST['is_veg']),
            'sort_order'  => (int)($_POST['sort_order']??0),
        ]);
        flash('menu','Menu item added.','success');
        redirect('/admin/menu');
    }
    public static function updateMenuItem(): void {
        requireLogin(); requireAdmin(); verifyCsrf();
        $id = (int)($_POST['id']??0);
        if($id){
            MenuItem::update($id,[
                'category_id' => (int)($_POST['category_id']??0),
                'name'        => sanitize($_POST['name']??''),
                'description' => sanitize($_POST['description']??''),
                'price'       => $_POST['price']??0,
                'badge'       => sanitize($_POST['badge']??''),
                'is_available'=> isset($_POST['is_available']),
                'is_veg'      => isset($_POST['is_veg']),
                'sort_order'  => (int)($_POST['sort_order']??0),
            ]);
            flash('menu','Menu item updated.','success');
        }
        redirect('/admin/menu');
    }
    public static function deleteMenuItem(): void {
        requireLogin(); requireAdmin(); verifyCsrf();
        $id = (int)($_POST['id']??0);
        if($id){ MenuItem::delete($id); flash('menu','Menu item deleted.','success'); }
        redirect('/admin/menu');
    }
}
