<?php

namespace App\Controllers;

use App\Models\PDOFactory;
use App\Models\Favorite;

class FavoriteController extends Controller
{
    private function getDb()
    {
        $factory = new PDOFactory();
        return $factory->create([
            'dbhost' => 'localhost', 
            'dbname' => 'nhatro_db',
            'dbuser' => 'postgres',
            'dbpass' => '0000' 
        ]);
    }

    public function index()
    {
        if (!is_logged_in()) {
            header('Location: /login');
            exit();
        }

        $user = current_user();
        $db = $this->getDb();
        $model = new Favorite($db);
        
        $favorites = $model->getFavorites($user['id']);

        $this->render('listing.favorites', [
            'title' => 'Tin đã lưu',
            'favorites' => $favorites
        ]);
    }

    public function toggle($roomId)
    {
        if (!is_logged_in()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
            exit();
        }

        $user = current_user();
        $db = $this->getDb();
        $model = new Favorite($db);
        
        // Gọi hàm toggle và trả về định dạng giống JS yêu cầu
        $isFavorited = $model->toggleFavorite($user['id'], $roomId);

        echo json_encode(['success' => true, 'favorited' => $isFavorited]);
        exit();
    }
}