<?php

class ReviewController {
    /**
     * Submit a new guest review (Compliment or Complaint).
     */
    public static function submit(): void {
        verifyCsrf();

        $user = authUser();
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));

        $type = sanitize($_POST['type'] ?? 'compliment');
        if (!in_array($type, ['compliment', 'complaint'], true)) {
            $type = 'compliment';
        }

        $name = trim(sanitize($_POST['name'] ?? ''));
        if (empty($name) && $user) {
            $name = $user['name'];
        }

        $email = trim(sanitize($_POST['email'] ?? ''));
        if (empty($email) && $user) {
            $email = $user['email'];
        }

        $rating = (int)($_POST['rating'] ?? ($type === 'compliment' ? 5 : 3));
        $rating = max(1, min(5, $rating));

        $title = trim(sanitize($_POST['title'] ?? ''));
        if (mb_strlen($title) > 160) {
            $title = mb_substr($title, 0, 160);
        }

        $content = trim(sanitize($_POST['content'] ?? ''));
        $visitDate = trim(sanitize($_POST['visit_date'] ?? ''));
        if (!empty($visitDate) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $visitDate)) {
            $visitDate = null;
        }

        // Validation
        $errors = [];
        if (empty($name)) {
            $errors[] = 'Please provide your name.';
        }
        if (mb_strlen($content) < 8) {
            $errors[] = 'Please provide at least 8 characters describing your experience.';
        }
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please provide a valid email address.';
        }

        if (!empty($errors)) {
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(422);
                echo json_encode(['success' => false, 'errors' => $errors]);
                exit;
            }
            flash('review_msg', implode(' ', $errors), 'error');
            redirect('/#reviews');
            return;
        }

        // Compliments and constructive complaints are approved to promote transparency,
        // while allowing admin to moderate or reply from the operations portal.
        $reviewId = Review::create([
            'user_id'    => $user['id'] ?? null,
            'name'       => $name,
            'email'      => !empty($email) ? $email : null,
            'type'       => $type,
            'rating'     => $rating,
            'title'      => !empty($title) ? $title : null,
            'content'    => $content,
            'visit_date' => !empty($visitDate) ? $visitDate : null,
            'status'     => 'approved',
        ]);

        auditLog('SUBMIT_REVIEW', "Review #{$reviewId} ({$type}) submitted by {$name}");

        $successMessage = $type === 'compliment'
            ? 'Thank you for your gracious compliment! It has been added to our Guestbook.'
            : 'Thank you for sharing your feedback. Our operations team reads every critique to refine our craftsmanship.';

        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            $newReview = Review::findById($reviewId);
            echo json_encode([
                'success' => true,
                'message' => $successMessage,
                'review'  => $newReview,
                'stats'   => Review::stats()
            ]);
            exit;
        }

        flash('review_msg', $successMessage, 'success');
        redirect('/#reviews');
    }

    /**
     * JSON API to fetch reviews dynamically (by type).
     */
    public static function apiList(): void {
        header('Content-Type: application/json; charset=utf-8');
        $type = sanitize($_GET['type'] ?? '');
        if (!in_array($type, ['compliment', 'complaint'], true)) {
            $type = null;
        }
        $reviews = Review::all($type, 'approved', 50);
        $stats = Review::stats();
        echo json_encode(['success' => true, 'reviews' => $reviews, 'stats' => $stats]);
        exit;
    }
}
