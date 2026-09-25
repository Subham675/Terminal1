<?php
class AdminController {
    public static function dashboard(): void {
        requireLogin(); requireAdmin();
        $stats = [
            'users'       => User::count(),
            'bookings'    => Booking::count(),
            'pending'     => Booking::countByStatus('pending'),
            'confirmed'   => Booking::countByStatus('confirmed'),
            'menu_items'  => MenuItem::count(),
            'revenue'     => Payment::totalRevenue(),
            'paid_count'  => Payment::countByStatus('paid'),
            'refunded_count' => Payment::countByStatus('refunded'),
        ];
        $recent_bookings = Booking::recent(8);
        $revenue_by_day   = Payment::revenueByDay(14);
        $bookings_by_day  = Booking::bookingsByDay(14);
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
            auditLog('UPDATE_BOOKING_STATUS', "Booking #{$id} status set to {$status}");
            flash('bookings','Booking status updated.','success');
        }
        redirect('/admin/bookings');
    }
    public static function deleteBooking(): void {
        requireLogin(); requireAdmin(); verifyCsrf();
        $id = (int)($_POST['id'] ?? 0);
        if($id){
            Booking::delete($id);
            auditLog('DELETE_BOOKING', "Booking #{$id} deleted");
            flash('bookings','Booking deleted.','success');
        }
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
        if($id && in_array($role,['admin','user'])){
            User::update($id,['role'=>$role]);
            auditLog('UPDATE_USER_ROLE', "User #{$id} role set to {$role}");
            flash('users','Role updated.','success');
        }
        redirect('/admin/users');
    }
    public static function deleteUser(): void {
        requireLogin(); requireAdmin(); verifyCsrf();
        $id = (int)($_POST['id'] ?? 0);
        if($id && $id !== (int)authUser()['id']){
            User::delete($id);
            auditLog('DELETE_USER', "User #{$id} deleted");
            flash('users','User deleted.','success');
        }
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
        try {
            $imageUrl = FileUpload::handleMenuImage($_FILES['image'] ?? null);
        } catch (\RuntimeException $e) {
            flash('menu', $e->getMessage(), 'error');
            redirect('/admin/menu');
            return;
        }
        MenuItem::create([
            'category_id' => (int)($_POST['category_id']??0),
            'name'        => sanitize($_POST['name']??''),
            'description' => sanitize($_POST['description']??''),
            'price'       => $_POST['price']??0,
            'badge'       => sanitize($_POST['badge']??''),
            'is_available'=> isset($_POST['is_available']),
            'is_veg'      => isset($_POST['is_veg']),
            'image_url'   => $imageUrl,
            'sort_order'  => (int)($_POST['sort_order']??0),
        ]);
        auditLog('CREATE_MENU_ITEM', "Created menu item: " . sanitize($_POST['name'] ?? ''));
        flash('menu','Menu item added.','success');
        redirect('/admin/menu');
    }
    public static function updateMenuItem(): void {
        requireLogin(); requireAdmin(); verifyCsrf();
        $id = (int)($_POST['id']??0);
        if($id){
            $existing = MenuItem::findById($id);
            try {
                $imageUrl = FileUpload::handleMenuImage($_FILES['image'] ?? null);
            } catch (\RuntimeException $e) {
                flash('menu', $e->getMessage(), 'error');
                redirect('/admin/menu');
                return;
            }
            // Keep existing image if no new file was uploaded
            if ($imageUrl === null) {
                $imageUrl = $existing['image_url'] ?? null;
            } elseif (!empty($existing['image_url'])) {
                FileUpload::deleteMenuImage($existing['image_url']);
            }
            MenuItem::update($id,[
                'category_id' => (int)($_POST['category_id']??0),
                'name'        => sanitize($_POST['name']??''),
                'description' => sanitize($_POST['description']??''),
                'price'       => $_POST['price']??0,
                'badge'       => sanitize($_POST['badge']??''),
                'is_available'=> isset($_POST['is_available']),
                'is_veg'      => isset($_POST['is_veg']),
                'image_url'   => $imageUrl,
                'sort_order'  => (int)($_POST['sort_order']??0),
            ]);
            auditLog('UPDATE_MENU_ITEM', "Updated menu item #{$id} (" . sanitize($_POST['name'] ?? '') . ")");
            flash('menu','Menu item updated.','success');
        }
        redirect('/admin/menu');
    }
    public static function deleteMenuItem(): void {
        requireLogin(); requireAdmin(); verifyCsrf();
        $id = (int)($_POST['id']??0);
        if($id){
            $existing = MenuItem::findById($id);
            if ($existing && !empty($existing['image_url'])) FileUpload::deleteMenuImage($existing['image_url']);
            MenuItem::delete($id);
            auditLog('DELETE_MENU_ITEM', "Deleted menu item #{$id} (" . ($existing['name'] ?? '') . ")");
            flash('menu','Menu item deleted.','success');
        }
        redirect('/admin/menu');
    }
}
