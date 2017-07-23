<?php namespace App\Http\Controllers;

class UserController extends Controller {


    /**
     * http://localhost:8889/user/profile
     *
     */
    public function showProfile()
    {
        echo 'url: user/profile';
    }

    /**
     * http://localhost:8889/user/profile3
     *
     */
    public function showProfile3() {

        echo 'url: user/profile3';
    }
}
