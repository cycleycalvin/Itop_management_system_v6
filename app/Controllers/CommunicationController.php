<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Security;
use App\Core\View;
use App\Models\Message;
use App\Models\Notification;

final class CommunicationController
{
    public function messages(): void
    {
        Auth::requireLogin();
        $model = new Message();
        $conversationId = (int) ($_GET['conversation_id'] ?? 0);
        View::render('communication/messages', [
            'conversations' => $model->conversations((int) Auth::id(), Security::cleanString($_GET['q'] ?? '')),
            'messages' => $conversationId ? $model->messages($conversationId, (int) Auth::id()) : [],
            'contacts' => $model->contacts((int) Auth::id()),
            'conversationId' => $conversationId,
            'q' => Security::cleanString($_GET['q'] ?? ''),
        ]);
    }

    public function sendMessage(): void
    {
        Auth::requireLogin();
        Security::verifyCsrf();
        $model = new Message();
        $attachment = $this->storeAttachment();
        $conversationId = (int) ($_POST['conversation_id'] ?? 0);
        $body = Security::cleanString($_POST['body'] ?? '', 3000);
        if ($conversationId) {
            $model->reply($conversationId, (int) Auth::id(), $body, $attachment);
        } else {
            $conversationId = $model->start((int) Auth::id(), (int) ($_POST['receiver_id'] ?? 0), Security::cleanString($_POST['subject'] ?? 'Conversation'), $body, $attachment);
        }

        $this->jsonOrRedirect(['ok' => true, 'conversation_id' => $conversationId], 'index.php?page=messages&conversation_id=' . $conversationId);
    }

    public function messagesFeed(): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');
        echo json_encode((new Message())->messages((int) ($_GET['conversation_id'] ?? 0), (int) Auth::id()));
    }

    public function deleteMessage(): void
    {
        Auth::requireLogin();
        Security::verifyCsrf();
        (new Message())->deleteMessage((int) ($_POST['id'] ?? 0), (int) Auth::id());
        $this->jsonOrRedirect(['ok' => true], 'index.php?page=messages');
    }

    public function notifications(): void
    {
        Auth::requireLogin();
        $model = new Notification();
        View::render('communication/notifications', [
            'notifications' => $model->recent((int) Auth::id(), Security::cleanString($_GET['q'] ?? ''), Security::cleanString($_GET['type'] ?? '')),
            'types' => $model->types(),
            'q' => Security::cleanString($_GET['q'] ?? ''),
            'type' => Security::cleanString($_GET['type'] ?? ''),
        ]);
    }

    public function markNotificationRead(): void
    {
        Auth::requireLogin();
        Security::verifyCsrf();
        $model = new Notification();
        if (isset($_POST['all'])) {
            $model->markAllRead((int) Auth::id());
        } else {
            $model->markRead((int) Auth::id(), (int) ($_POST['id'] ?? 0));
        }
        $this->jsonOrRedirect(['ok' => true], 'index.php?page=notifications');
    }

    public function deleteNotification(): void
    {
        Auth::requireLogin();
        Security::verifyCsrf();
        (new Notification())->delete((int) Auth::id(), (int) ($_POST['id'] ?? 0));
        $this->jsonOrRedirect(['ok' => true], 'index.php?page=notifications');
    }

    public function badgeCounts(): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');
        echo json_encode([
            'messages' => (new Message())->unreadCount((int) Auth::id()),
            'notifications' => (new Notification())->unreadCount((int) Auth::id()),
        ]);
    }

    private function storeAttachment(): ?string
    {
        if (empty($_FILES['attachment']['name'])) {
            return null;
        }
        $filename = Security::validateUpload($_FILES['attachment'], ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'zip']);
        if (!$filename) {
            return null;
        }
        move_uploaded_file($_FILES['attachment']['tmp_name'], UPLOAD_PATH . '/' . $filename);
        return $filename;
    }

    private function jsonOrRedirect(array $payload, string $redirect): void
    {
        if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode($payload);
            return;
        }
        header('Location: ' . $redirect);
    }
}
