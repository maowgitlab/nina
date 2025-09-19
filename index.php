<?php
session_start();
$current_page = basename($_SERVER["PHP_SELF"]);
if (!isset($_SESSION["ses_username"]) && $current_page !== "index.php") {
    header("Location: beranda.php");
    exit();
} elseif (isset($_SESSION["ses_username"])) {
    $data_id = $_SESSION["ses_id"];
    $data_nama = $_SESSION["ses_nama"];
    $data_user = $_SESSION["ses_username"];
}
include "koneksi.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login | Sistem Inventaris</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="dist/img/izin.png">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #4e54c8, #8f94fb);
            margin: 0;
            padding: 0;
            display: flex;
            height: 100vh;
            align-items: center;
            justify-content: center;
        }

        .login-box {
            background: #fff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .login-box img {
            width: 80px;
            margin-bottom: 20px;
        }

        .login-box h2 {
            font-weight: 600;
            margin-bottom: 30px;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 30px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background-color: #6c63ff;
            color: white;
            border: none;
            border-radius: 30px;
            font-weight: bold;
            font-size: 16px;
            transition: 0.3s ease;
            cursor: pointer;
        }

        .btn-login:hover {
            background-color: #5548d9;
        }

        .register-link {
            margin-top: 20px;
            font-size: 14px;
        }

        .register-link a {
            color: #6c63ff;
            text-decoration: none;
            font-weight: 600;
        }

        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="login-box">
        <img src="img/logo disdag.png" alt="Logo">
        <h2>Sistem Inventaris</h2>
        <form action="" method="post">
            <input type="text" class="form-control" name="username" placeholder="Username" required>
            <input type="password" class="form-control" name="password" placeholder="Password" required>
            <button type="submit" class="btn-login" name="btnLogin">Login</button>
        </form>
        <div class="register-link">
            Belum punya akun? <a href="register.php">Buat Akun</a>
        </div>
    </div>
</body>

</html>

<?php
if (isset($_POST['btnLogin'])) {
    if (!isset($conn) || !$conn) {
        die("Koneksi tidak tersedia.");
    }

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $sql_login = "SELECT * FROM user WHERE BINARY username='$username' AND password='$password'";
    $query_login = mysqli_query($conn, $sql_login);

    if ($query_login) {
        $data_login = mysqli_fetch_assoc($query_login);
        if ($data_login) {
            $_SESSION["ses_id"] = $data_login["id_user"];
            $_SESSION["ses_nama"] = $data_login["nama"];
            $_SESSION["ses_username"] = $data_login["username"];
            $_SESSION["ses_password"] = $data_login["password"];
            $_SESSION["ses_level"] = $data_login["level"];

            echo "<script> 
                Swal.fire({ 
                    title: 'Login Berhasil', 
                    icon: 'success', 
                    confirmButtonText: 'OK' 
                }).then(() => { 
                    window.location = 'beranda.php'; 
                }); 
            </script>";
        } else {
            echo "<script> 
                Swal.fire({ 
                    title: 'Login Gagal', 
                    text: 'Periksa kembali username dan password Anda.', 
                    icon: 'error', 
                    confirmButtonText: 'OK' 
                }); 
            </script>";
        }
    } else {
        die("Query error: " . mysqli_error($conn));
    }
}
?>