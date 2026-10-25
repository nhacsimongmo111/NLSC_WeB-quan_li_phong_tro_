<?php

namespace App\Controllers;

use App\Models\Room;

class HomeController extends Controller
{
    public function index(): void
    {
        ensure_logged_in();

        $roomModel = new Room(PDO());
        $stats = $roomModel->getSummaryStats();
        
        $statusFilter = $_GET['status'] ?? null;
        $rooms = $roomModel->getAllWithDetails($statusFilter);

        $this->render('home/index', [
            'stats' => $stats,
            'rooms' => $rooms,
            'currentFilter' => $statusFilter,
            'flash_success' => session_get_once('flash_success'),
            'flash_error' => session_get_once('flash_error'),
        ]);
    }
}
