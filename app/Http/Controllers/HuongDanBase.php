<?php

namespace  App\Http\Controllers;

use App\Models\User;
use App\Traits\TraitCRUD;

class HuongDanBase extends Controller
{
    use TraitCRUD;

    public function __construct(
        protected User $model
    ) {}


    public function index_role3()
    {
        return view('admin.role3_test_dashboard');
    }

    public function index_test_role()
    {
        return view('admin.test_dashboard');
    }

    public function admin_login()
    {
        return view('admin.login');
    }

    public function role3_login()
    {
        return view('admin.role3_login');
    }

    // TODO: Ghi đè method TraitCRUD
    // public function index() {
    //     dd('test');
    // }
}
