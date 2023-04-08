<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChildResource;
use App\Models\Child;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ChildController extends Controller
{
    public function index()
    {
        $child = Child::latest()->paginate(5);
        return new ChildResource(true, 'Child list', $child);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'name' => 'required',
            'age' => 'required',
            'id_koor' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $image = $request->file('image');
        // $image->storeAs('public/child', $image->hashName());

        $filename = $image->getClientOriginalName();
        $image->move(public_path('asset/img'), $filename);

        $child = Child::create([
            // 'image' => $image->hashName(),
            'image' => $filename,
            'name' => $request->name,
            'age' => $request->age,
            'id_koor' => $request->id_koor
        ]);

        return new ChildResource(true, 'Child created', $child);

    }

    public function show($id)
    {
        $child = Child::find($id);
        return new ChildResource(true, 'Child detail', $child);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'age' => 'required',
            'id_koor' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $child = Child::find($id);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $image->storeAs('public/child', $image->hashName());

            Storage::delete('public/child/' .basename($child->image));

            $child->update([
                'image' => $image->hashName(),
                'name' => $request->name,
                'age' => $request->age,
                'id_koor' => $request->id_koor
            ]);
        } else {
            $child->update([
                'name' => $request->name,
                'age' => $request->age,
                'id_koor' => $request->id_koor
            ]);
        }

        return new ChildResource(true, 'Child updated', $child);
    }

    public function destroy($id)
    {
        $child = Child::find($id);
        Storage::delete('public/child/' .basename($child->image));
        $child->delete();

        return new ChildResource(true, 'Child deleted', $child);
    }
}
