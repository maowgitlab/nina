<?php
// Memulai session sebelum ada output HTML
session_start();
include "";  // Menghubungkan ke file koneksi
 
    if (isset ($_POST['Simpan'])){ 
    //mulai proses simpan data 
        $sql_simpan = "INSERT INTO tb_pengguna (nama_pengguna,username,password,level) VALUES ( 
        '".$_POST['nama_pengguna']."', 
        '".$_POST['username']."', 
        '".$_POST['password']."', 
        '".$_POST['level']."')"; 
        $query_simpan = mysqli_query($koneksi, $sql_simpan); 
        mysqli_close($koneksi); 
 
    if ($query_simpan) { 
      echo "<script> 
      Swal.fire({title: 'Registrasi Berhasil',text: '',icon: 'success',confirmButtonText: 'OK' 
      }).then((result) => {if (result.value){ 
          window.location = 'login.php'; 
          } 
      })</script>"; 
      }else{ 
      echo "<script> 
      Swal.fire({title: 'Registrasi Gagal',text: '',icon: 'error',confirmButtonText: 'OK' 
      }).then((result) => {if (result.value){ 
          window.location = 'buatakun.php'; 
          } 
      })</script>"; 
    }}

     else {
        // Menampilkan alert gagal menggunakan Swal
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@10'></script>";
        echo "<script>
            Swal.fire({
                title: 'Registrasi Gagal',
                text: 'Terjadi kesalahan, silakan coba lagi.',
                icon: 'error',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location = 'register.php';
                }
            })
        </script>";
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Register</title>
    <link rel="icon" href="dist/img/izin.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Source Sans Pro', sans-serif;
        }
        .register-box {
            width: 400px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
            text-align: center;
        }
        .register-logo img {
            width: 100px;
            margin-bottom: 20px;
        }
        .btn-register {
            background-color: #28a745;
            color: white;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.3s ease;
            border-radius: 30px;
        }
        .btn-register:hover {
            background-color: #218838;
            transform: translateY(-3px);
        }
        .form-control {
            border-radius: 30px;
            padding: 10px 15px;
        }
        .input-group-append .input-group-text {
            border-radius: 0 30px 30px 0;
            background-color: #28a745;
            color: white;
            border: none;
        }
        .card-body {
            padding: 2rem;
        }
        .register-card-body {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            padding: 30px;
        }
    </style>
</head>
<body class="hold-transition register-page">
    <div class="register-box">
        <div class="register-logo">
            <img src="img/logo disdag.png" alt="Logo" />
        </div>
        <div class="card">
            <div class="card-body register-card-body">
                <h5 class="text-center"><b>Registrasi Pengguna</b></h5>
                <br>
                <form action="" method="post">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" name="nama" placeholder="Nama" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" name="username" placeholder="Username" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-5">
                        <input type="password" class="form-control" name="password" placeholder="Password" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-5 mt-5">
                        <div class="col-12">
                            <button type="submit" class="btn btn-register btn-block" name="btnRegister" title="Daftar">
                                Daftar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="dist/js/adminlte.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
</body>
</html>


   
