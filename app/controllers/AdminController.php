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
            'reviews'     => Review::stats(),
        ];
        $recent_bookings = Booking::recent(8);
        $recent_reviews  = Review::all(null, null, 6);
        $revenue_by_day   = Payment::revenueByDay(14);
        $bookings_by_day  = Booking::bookingsByDay(14);
        require APP_ROOT.'/app/views/admin/dashboard.php';
    }

    // ── BOOKINGS ──────────────────────────────────────────
    public static function bookings(): void {
        requireLogin(); requireAdmin();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 15;
        $search = sanitize($_GET['q'] ?? '');
        $status = sanitize($_GET['status'] ?? '');

        $where = ["1=1"];
        $params = [];
        if ($search !== '') {
            $where[] = "(b.name LIKE ? OR b.phone LIKE ? OR b.email LIKE ? OR b.occasion LIKE ? OR CAST(b.id AS CHAR) = ?)";
            $wild = "%{$search}%";
            $params = array_merge($params, [$wild, $wild, $wild, $wild, $search]);
        }
        if ($status !== '' && in_array($status, ['pending', 'confirmed', 'cancelled', 'completed', 'archived'])) {
            $where[] = "b.status = ?";
            $params[] = $status;
        } else {
            // By default, hide archived records from primary view unless requested
            $where[] = "b.status != 'archived'";
        }
        $whereSql = implode(' AND ', $where);

        $totalCount = (int) (Database::row("SELECT COUNT(*) as c FROM bookings b WHERE {$whereSql}", $params)['c'] ?? 0);
        $totalPages = max(1, (int)ceil($totalCount / $perPage));
        $offset = ($page - 1) * $perPage;

        $bookings = Database::rows(
            "SELECT b.*, u.name as user_name FROM bookings b LEFT JOIN users u ON b.user_id = u.id WHERE {$whereSql} ORDER BY b.created_at DESC LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $pagination = [
            'total'       => $totalCount,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => $totalPages,
            'search'      => $search,
            'status'      => $status
        ];

        require APP_ROOT.'/app/views/admin/bookings.php';
    }
    public static function updateBookingStatus(): void {
        requireLogin(); requireAdmin(); verifyCsrf();
        $id     = (int)($_POST['id'] ?? 0);
        $status = sanitize($_POST['status'] ?? '');
        if($id && in_array($status,['pending','confirmed','cancelled','completed','archived'])){
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
            Booking::archive($id);
            auditLog('ARCHIVE_BOOKING', "Booking #{$id} archived");
            flash('bookings','Booking archived from active ledger.','success');
        }
        redirect('/admin/bookings');
    }

    // ── USERS ─────────────────────────────────────────────
    public static function users(): void {
        requireLogin(); requireAdmin();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 15;
        $search = sanitize($_GET['q'] ?? '');
        $role = sanitize($_GET['role'] ?? '');

        $where = ["1=1"];
        $params = [];
        if ($search !== '') {
            $where[] = "(name LIKE ? OR email LIKE ? OR phone LIKE ? OR CAST(id AS CHAR) = ?)";
            $wild = "%{$search}%";
            $params = array_merge($params, [$wild, $wild, $wild, $search]);
        }
        if ($role !== '' && in_array($role, ['admin', 'user'])) {
            $where[] = "role = ?";
            $params[] = $role;
        }
        $whereSql = implode(' AND ', $where);

        $totalCount = (int) (Database::row("SELECT COUNT(*) as c FROM users WHERE {$whereSql}", $params)['c'] ?? 0);
        $totalPages = max(1, (int)ceil($totalCount / $perPage));
        $offset = ($page - 1) * $perPage;

        $users = Database::rows(
            "SELECT * FROM users WHERE {$whereSql} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $pagination = [
            'total'       => $totalCount,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => $totalPages,
            'search'      => $search,
            'role'        => $role
        ];

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

    // ── GUEST REVIEWS (COMPLIMENTS & COMPLAINTS) ───────────
    public static function reviews(): void {
        requireLogin(); requireAdmin();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 15;
        $search = sanitize($_GET['q'] ?? '');
        $status = sanitize($_GET['status'] ?? '');
        $type   = sanitize($_GET['type'] ?? '');

        $where = ["1=1"];
        $params = [];
        if ($search !== '') {
            $where[] = "(name LIKE ? OR email LIKE ? OR title LIKE ? OR content LIKE ?)";
            $wild = "%{$search}%";
            $params = array_merge($params, [$wild, $wild, $wild, $wild]);
        }
        if ($status !== '' && in_array($status, ['approved', 'hidden', 'pending'])) {
            $where[] = "status = ?";
            $params[] = $status;
        }
        if ($type !== '' && in_array($type, ['compliment', 'complaint'])) {
            $where[] = "type = ?";
            $params[] = $type;
        }
        $whereSql = implode(' AND ', $where);

        $totalCount = (int) (Database::row("SELECT COUNT(*) as c FROM reviews WHERE {$whereSql}", $params)['c'] ?? 0);
        $totalPages = max(1, (int)ceil($totalCount / $perPage));
        $offset = ($page - 1) * $perPage;

        $reviews = Database::rows(
            "SELECT * FROM reviews WHERE {$whereSql} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}",
            $params
        );
        $stats   = Review::stats();

        $pagination = [
            'total'       => $totalCount,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => $totalPages,
            'search'      => $search,
            'status'      => $status,
            'type'        => $type
        ];

        require APP_ROOT.'/app/views/admin/reviews.php';
    }

    public static function updateReviewStatus(): void {
        requireLogin(); requireAdmin(); verifyCsrf();
        $id     = (int)($_POST['id'] ?? 0);
        $status = sanitize($_POST['status'] ?? '');
        if ($id && in_array($status, ['approved', 'pending', 'hidden'], true)) {
            Review::updateStatus($id, $status);
            auditLog('UPDATE_REVIEW_STATUS', "Review #{$id} status set to {$status}");
            flash('reviews', "Review #{$id} status updated to {$status}.", 'success');
        }
        redirect('/admin/reviews');
    }

    public static function replyReview(): void {
        requireLogin(); requireAdmin(); verifyCsrf();
        $id    = (int)($_POST['id'] ?? 0);
        $reply = trim(sanitize($_POST['admin_reply'] ?? ''));
        if ($id) {
            Review::updateReply($id, !empty($reply) ? $reply : null);
            auditLog('REPLY_REVIEW', "Official response saved for Review #{$id}");
            flash('reviews', "Official management response saved for Review #{$id}.", 'success');
        }
        redirect('/admin/reviews');
    }

    public static function deleteReview(): void {
        requireLogin(); requireAdmin(); verifyCsrf();
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            Review::delete($id);
            auditLog('DELETE_REVIEW', "Deleted Review #{$id}");
            flash('reviews', "Review #{$id} removed successfully.", 'success');
        }
        redirect('/admin/reviews');
    }
}

