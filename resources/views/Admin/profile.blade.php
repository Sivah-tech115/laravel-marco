@extends('Admin.layouts.main')

@section('title', 'Altitude Roofing')
@section('breadcrumbtitle', 'User')
@section('breadcrumbtitle2', 'View Profile')

@section('styles')
<style>
    .form-label {
        font-weight: 600;
        margin-bottom: 5px;
    }

    .profile-img-preview {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 50%;
        border: 2px solid #ddd;
        margin-bottom: 10px;
    }
</style>
@endsection

@section('content')
<div class="page-wrapper">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10 col-sm-12">

            {{-- Success & Error Alerts --}}
            @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Profile Info Form --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Edit Profile</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="changeprofile" class="form-control" value="changeprofile">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Profile Image</label><br>
                                @if($user && $user->image)
                                <img id="imagePreview" src="{{ asset('storage/users/' . $user->image) }}" class="profile-img-preview" alt="Avatar">
                                @else
                                <img id="imagePreview" src="{{ asset('assets/images/avatar-default.jpg') }}" class="profile-img-preview" alt="Default Avatar">
                                @endif
                                <input type="file" name="image" id="imageInput" class="form-control mt-2">
                                @error('image') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Name</label>
                                <input type="text" name="fname" value="{{ old('fname', $user->name) }}" class="form-control">
                                @error('fname') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>


                            <div class="col-md-12">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control">
                                @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>


                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary px-4">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Password Change Form --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Change Password</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.profile.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="changepassbtn" class="form-control" value="changepassbtn">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">New Password</label>
                                <input type="password" name="password" class="form-control">
                                @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="password_confirmation" class="form-control">
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-warning px-4">Change Password</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById("imageInput").addEventListener("change", function(e) {
        const [file] = this.files;
        if (file) {
            document.getElementById("imagePreview").src = URL.createObjectURL(file);
        }
    });
</script>
@endsection