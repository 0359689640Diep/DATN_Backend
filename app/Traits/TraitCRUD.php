<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait TraitCRUD
{
    public function index()
    {
        $data = $this->model->latest('id')->paginate(10);
        $view = $this->model->getTable() . __FUNCTION__;

        return view($view, $data);
    }

    public function show($id)
    {
        $data = $this->model->findOrFail($id);
        $view = $this->model->getTable() . __FUNCTION__;

        return view($view, $data);
    }

    public function store(Request $request)
    {
        $this->model->create($request->all());
        $notification = [
            'success' => "create successfully",
        ];

        return redirect()->route($this->model->getTable() . '.index')->with($notification);
    }

    public function create()
    {
        $view = $this->model->getTable() . __FUNCTION__;

        return view($view);
    }

    public function edit($id)
    {
        $data = $this->model->findOrFail($id);
        $view = $this->model->getTable() . __FUNCTION__;

        return view($view, $data);
    }

    public function update(Request $request, $id)
    {
        $model = $this->model->findOrFail($id);
        $model->update($request->all());
        $notification = [
            'success' => "update successfully",
        ];

        return redirect()->back()->with($notification);
    }

    public function delete(Request $request, $id)
    {
        $model = $this->model->findOrFail($id);
        $model->delete();

        $notification = [
            'success' => "delete successfully",
        ];

        return redirect()->back()->with($notification);
    }
}
