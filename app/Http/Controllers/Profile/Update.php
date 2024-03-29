<?php

namespace App\Http\Controllers\Profile;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class Update extends Component
{
    use WithFileUploads;
    public $image, $name, $username, $email, $phone, $address , $password, $confirm_password;
    public function render()
    {
        return view('profile.update');
    }
    public function updatedImage()
    {
        $this->validate([
            'image' => 'image|max:20000|mimes:jpeg,jpg,png,gif,svg',
        ], [
            'image.image' => __('validation.image', ['attribute' => __('header.image')]),
            'image.max' => __('validation.max.file', ['attribute' => __('header.image'), 'max' => 20000 . "MB"]),
            'image.mimes' => __('validation.mimes', ['attribute' => __('header.image'), 'values' => 'jpeg,jpg,png,gif,svg']),
        ]);
        $old_image = auth()->user()->user_details->image;
        if ($old_image != null) {
            Storage::delete('public/users/' . $old_image);
        }
        $ResizeImage = Image::make($this->image)->resize(350, 300, function ($constraint) {
            $constraint->aspectRatio();
        });
        $ResizeImage->stream();
        $imageName = time() . '-' . uniqid() . '-' . uniqid() . '.' . $this->image->GetClientOriginalExtension();
        Storage::put('public/users/' . $imageName, $ResizeImage);
        // $this->image->storeAs('public/users', $imageName);
        auth()->user()->user_details->update([
            'image' => $imageName,
        ]);
        flash()->addSuccess('header.updated');
        $this->emit('UpdateProfile');
        $this->image = null;
    }
    public function deleteImage($Image)
    {
        if ($Image != null) {
            Storage::delete('public/users/' . $Image);
            auth()->user()->user_details->update([
                'image' => null,
            ]);
            flash()->addSuccess('header.deleted');
            $this->emit('UpdateProfile');
        } else {
            flash()->addWarning('header.no_image');
        }
        $this->emit('UpdateProfile');
    }
    public function done()
    {

        $this->resetValidation();
        $this->dispatchBrowserEvent('closeModal');
    }
    public function edit()
    {
        $this->name = auth()->user()->name;
        $this->username = auth()->user()->username;
        $this->email = auth()->user()->email;
        $this->phone = auth()->user()->phone;
        $this->address = auth()->user()->user_details->address;
    }
    public function submit()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . auth()->user()->id,
            'email' => 'required|string|email|max:255|unique:users,email,' . auth()->user()->id,
            'phone' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ], [
            'name.required' => __('validation.required', ['attribute' => __('header.name')]),
            'name.string' => __('validation.string', ['attribute' => __('header.name')]),
            'name.max' => __('validation.max.string', ['attribute' => __('header.name'), 'max' => 255]),
            'username.required' => __('validation.required', ['attribute' => __('header.username')]),
            'username.string' => __('validation.string', ['attribute' => __('header.username')]),
            'username.max' => __('validation.max.string', ['attribute' => __('header.username'), 'max' => 255]),
            'username.unique' => __('validation.unique', ['attribute' => __('header.username')]),
            'email.required' => __('validation.required', ['attribute' => __('header.email')]),
            'email.string' => __('validation.string', ['attribute' => __('header.email')]),
            'email.email' => __('validation.email', ['attribute' => __('header.email')]),
            'email.max' => __('validation.max.string', ['attribute' => __('header.email'), 'max' => 255]),
            'email.unique' => __('validation.unique', ['attribute' => __('header.email')]),
            'phone.required' => __('validation.required', ['attribute' => __('header.phone')]),
            'phone.string' => __('validation.string', ['attribute' => __('header.phone')]),
            'phone.max' => __('validation.max.string', ['attribute' => __('header.phone'), 'max' => 255]),
            'address.required' => __('validation.required', ['attribute' => __('header.address')]),
            'address.string' => __('validation.string', ['attribute' => __('header.address')]),
            'address.max' => __('validation.max.string', ['attribute' => __('header.address'), 'max' => 255]),
        ]);

        auth()->user()->update([
            'name' => $this->name,
            'username' => $this->username,
            'phone' => $this->phone,
            'email' => $this->email,
        ]);
        auth()->user()->user_details->update([
            'address' => $this->address,
        ]);
        flash()->addSuccess('header.updated');
        $this->emit('UpdateProfile');
        $this->done();
    }
    public function ChangePassword()
    {
        $this->validate([
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|string|same:password',
        ], [
            'password.required' => __('validation.required', ['attribute' => __('header.password')]),
            'password.string' => __('validation.string', ['attribute' => __('header.password')]),
            'password.min' => __('validation.min.string', ['attribute' => __('header.password'), 'min' => 8]),
            'confirm_password.required' => __('validation.required', ['attribute' => __('header.confirm_password')]),
            'confirm_password.string' => __('validation.string', ['attribute' => __('header.confirm_password')]),
            'confirm_password.same' => __('validation.same', ['attribute' => __('header.confirm_password'), 'other' => __('header.password')]),
        ]);
        auth()->user()->update([
            'password' => Hash::make($this->password),
        ]);
        flash()->addSuccess('header.updated');
        $this->reset('password', 'confirm_password');
        $this->done();
    }
}
