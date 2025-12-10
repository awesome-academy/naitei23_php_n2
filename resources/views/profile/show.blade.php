<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
</head>
<body style="font-family: Arial, sans-serif; margin: 40px;">
    <h1>My Profile</h1>
    
    @if(session('success'))
        <div style="color: green; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0;">
            {{ session('success') }}
        </div>
    @endif
    
    <div style="margin: 20px 0;">
        @if($user->profile_avatar_url)
            <img src="{{ asset('storage/' . $user->profile_avatar_url) }}" 
                 alt="Avatar" 
                 style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%;">
        @else
            <div style="width:150px;height:150px;background:#ccc;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                No Avatar
            </div>
        @endif
    </div>
    
    <div style="margin: 20px 0;">
        <p><strong>Name:</strong> {{ $user->full_name }}</p>
        <p><strong>Email:</strong> {{ $user->email }}</p>
        <p><strong>Phone:</strong> {{ $user->phone_number ?? 'Not set' }}</p>
        <p><strong>Bio:</strong> {{ $user->bio ?? 'Not set' }}</p>
        <p><strong>Language:</strong> {{ $user->locale ?? 'en' }}</p>
    </div>
    
    <div style="margin: 20px 0;">
        <a href="{{ route('profile.edit') }}" 
           style="padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 4px;">
            Edit Profile
        </a>
        <a href="/" 
           style="padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; margin-left: 10px;">
            Back to Home
        </a>
    </div>
</body>
</html>


