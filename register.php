<?php
include "koneksi.php";

if (isset($_POST['register'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $level = mysqli_real_escape_string($conn, $_POST['level']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $query = mysqli_query($conn, "INSERT INTO user (nama, username, password, level) VALUES ('$nama', '$username', '$password', '$level')");
    if ($query) {
        echo "<script>alert('Registrasi berhasil'); window.location='index.php';</script>";
    } else {
        echo "<script>alert('Registrasi gagal');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Registrasi</title>
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color:rgb(53, 146, 253);
            font-family: Arial, sans-serif;
        }

        .box {
            width: 360px;
            padding: 40px;
            background: #f4f4f4;
            margin: 100px auto;
            text-align: center;
            border-radius: 10px;
            position: relative;
        }

        .box .avatar {
            width: 80px;
            height: 80px;
            background:rgb(76, 135, 175);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            position: absolute;
            top: -40px;
            left: 50%;
            transform: translateX(-50%);
        }

        .box h2 {
            margin: 50px 0 20px;
        }

        .box input[type="text"],
        .box input[type="password"],
        .box select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            background: #ddd;
            border: none;
            border-radius: 5px;
        }

        .box input[type="submit"] {
            background:rgb(76, 144, 175);
            color: white;
            border: none;
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .box .login-link {
            margin-top: 10px;
            font-size: 14px;
        }

        .box .login-link a {
            color: #007BFF;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <form class="box" method="POST" action="">
        <div class="avatar">
            <i class="fas fa-user"></i>
        </div>
        <h2>Registrasi</h2>
        <input type="text" name="nama" placeholder="Nama" required>
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <select name="level" required>
            <option value="">-- Pilih Level --</option>
            <option value="admin">Admin</option>
            <option value="auditor">Auditor</option>
            <option value="pegawai">Pegawai</option>
        </select>
        <input type="submit" name="register" value="REGISTER">
        <div class="login-link">
            Back to <a href="index.php">Login</a>
        </div>
    </form>
</body>

</html>
