<?php require_once __DIR__ . '/../config/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | LearnsDecode</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        body { 
            font-family: 'Poppins', sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            min-height: 100vh; 
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .header { text-align: center; color: white; margin-bottom: 20px; }
        .header h1 { font-size: 3rem; font-weight: 700; letter-spacing: -1px; }
        
        .card {
            border-radius: 20px;
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
        .btn-primary {
            background: #667eea;
            border: none;
            transition: 0.3s;
        }
        .btn-primary:hover {
            background: #764ba2;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .form-control {
            border-radius: 10px;
            padding: 12px;
            border: 1px solid #eee;
        }
        .form-control:focus {
            box-shadow: 0 0 10px rgba(102, 126, 234, 0.2);
            border-color: #667eea;
        }
    </style>
</head>
<body>
    <div class="header animate__animated animate__fadeInDown">
        <h1>Learns<span style="color: #ffde59;">Decode</span></h1>
        <p>Your Gateway to Quality Learning</p>
    </div>