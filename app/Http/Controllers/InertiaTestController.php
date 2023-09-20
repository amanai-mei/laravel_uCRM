<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\InertiaTest;

class InertiaTestController extends Controller
{
    public function index()
    {
        return Inertia::render('Inertia/Index', [
            'blogs' => InertiaTest::all()
        ]);
    }

    public function show($id)
    {
        return Inertia::render('Inertia/Show',
        [
            'id' => $id,
            'blog' => InertiaTest::findOrFail($id)
        ]);
    }

    public function create()
    {
        return Inertia::render('Inertia/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'max:20'],
            'content' => ['required'],
        ]);

        $inertiatest = new InertiaTest; //インスタンス化
        $inertiatest->title = $request->title;
        $inertiatest->content = $request->content;
        $inertiatest->save();

        return to_route('inertia.index')
        ->with([
            'message' => '登録しました。'
        ]);
    }
    public function delete($id)
    {
        $book = InertiaTest::findOrFail($id);
        $book->delete();

        return to_route('inertia.index')
        ->with([
            'message' => '削除しました。'
        ]);

    }
}
