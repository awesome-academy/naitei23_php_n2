<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
</head>
<body style="font-family: Arial, sans-serif; margin: 40px;">
    <h1>Edit Profile</h1>
    
    @if(session('success'))
        <div style="color: green; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0;">
            {{ session('success') }}
        </div>
    @endif
    
    @if($errors->any())
        <div style="color: red; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0;">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" style="max-width: 600px;">
        @csrf
        @method('PUT')
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Full Name *</label>
            <input type="text" 
                   name="full_name" 
                   value="{{ old('full_name', $user->full_name) }}" 
                   required
                   style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            @error('full_name')
                <div style="color: red; font-size: 14px; margin-top: 5px;">{{ $message }}</div>
            @enderror
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Email *</label>
            <input type="email" 
                   name="email" 
                   value="{{ old('email', $user->email) }}" 
                   required
                   style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            @error('email')
                <div style="color: red; font-size: 14px; margin-top: 5px;">{{ $message }}</div>
            @enderror
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Phone Number</label>
            <input type="text" 
                   name="phone_number" 
                   value="{{ old('phone_number', $user->phone_number) }}"
                   style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Bio</label>
            <textarea name="bio" 
                      style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; height: 100px;">{{ old('bio', $user->bio) }}</textarea>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Avatar</label>
            <input type="file" name="avatar" accept="image/*">
            
            @if($user->profile_avatar_url)
                <div style="margin-top: 10px;">
                    <p>Current Avatar:</p>
                    <img src="{{ asset('storage/' . $user->profile_avatar_url) }}" 
                         alt="Avatar" 
                         style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%;">
                </div>
            @endif
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Language</label>
            <select name="locale" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                <option value="en" {{ ($user->locale ?? 'en') == 'en' ? 'selected' : '' }}>English</option>
                <option value="vi" {{ ($user->locale ?? 'en') == 'vi' ? 'selected' : '' }}>Vietnamese</option>
            </select>
        </div>
        
        <div style="display: flex; gap: 10px; margin-top: 20px;">
            <button type="submit" 
                    style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">
                Update Profile
            </button>
            <a href="{{ route('profile.show') }}" 
               style="padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px;">
                Cancel
            </a>
        </div>
    </form>
</body>
</html>


