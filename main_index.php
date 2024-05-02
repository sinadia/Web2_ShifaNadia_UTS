<?php
require_once "Perpus/perpustakaan.php";
require_once "Perpus/book.php";
session_start();

if (!isset($_SESSION['perpustakaan'])) {
    $_SESSION['perpustakaan'] = new perpustakaan();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['tambahBuku'])) {
        $judul = $_POST['judul'];
        $tahun = $_POST['tahun'];
        $penulis = $_POST['penulis'];
        $penerbit = $_POST['penerbit'];
        $isbn = $_POST['isbn'];
        

        $newBook = new ReferenceBook($judul, $tahun, $penulis, $penerbit, $isbn);
        $_SESSION['perpustakaan']->tambahBuku($newBook);
    }
    if (isset($_POST['hapusBuku'])) {

        if (isset($_POST['isbn'])) {

            $isbn = $_POST['isbn'];
            if (isset($_SESSION['perpustakaan'])) {
                $_SESSION['perpustakaan']->hapusBuku($isbn);
            }
        }
    }

    if (isset($_POST['pinjamBuku'])) {
        $isbn = $_POST['isbn'];
        $peminjam = $_POST['peminjam'];
        $tanggal_kembali = $_POST['tanggal'];

        if ($_SESSION['perpustakaan']->cekLimitPinjam($peminjam)) {
            $book = $_SESSION['perpustakaan']->cariBukuByISBN($isbn);

            if ($book) {
                $book->pinjamBuku($peminjam, $tanggal_kembali);
                $_SESSION['perpustakaan']->saveKeSession();
            }
        }
    }

    if (isset($_POST['kembalikanBuku'])) {
        $isbn = $_POST['isbn'];

        $book = $_SESSION['perpustakaan']->cariBukuByISBN($isbn);

        if ($book) {
            $book->kembalikanBuku();
            $_SESSION['perpustakaan']->saveKeSession();
        } else {
            echo "<script>alert('Tidak ada buku yang dikembalikan');</script>";
        }
    }
}
?>

<!doctype html>
<html lang="en">

<head>
<div class="container">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Digital Perpus</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="bootstrap/js/po"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        .card-book {
            width: 400px;
        }
        .custom-nav {
            background-color: white;
        }
        .container {
            background-color: pink;
        }
        .btn-success{
            background-color: purple;
        }
        .btn-pinjam{
            background-color: brown;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-light custom-nav">
        <div class="container">
        <a class="navbar-brand" href="#" style="font-family: 'Lucida Bright', sans-serif; font-weight: bold;">
    Perpustakaan Shifa Nadia
</a>
           
        </div>
    </nav>

    <div class="modal fade" id="modalPinjam" tabindex="-1" aria-labelledby="modalLabelPinjam" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalLabelPinjam">Pinjam Buku?</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                    <div class="modal-body">
                        <input type="hidden" class="form-control" id="pinjamISBN" name="isbn" required>
                        <div class="mb-3">
                            <label for="modalPeminjam" class="form-label">Nama Peminjam</label>
                            <input type="text" class="form-control" name="peminjam" id="modalPeminjam" required>
                        </div>
                        <div class="input-group date mb-3" id="datepicker">
                            <input type="date" class="form-control" name="tanggal" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tidak</button>
                        <button type="submit" name="pinjamBuku" class="btn btn-success">Ya</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalHapus" tabindex="-1" aria-labelledby="modalLabelHapus" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalLabelHapus">Hapus Buku?</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                    <div class="modal-body">
                        <p>Apakah anda ingin menghapus buku ini?</p>
                        <input type="hidden" name="isbn" id="hapusISBN" value="" />
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tidak</button>
                        <button type="submit" name="hapusBuku" class="btn btn-success">Ya</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="container">
    <div class="mx-auto px-5 my-3 d-flex justify-content-center align-items-end">
    <div class="container">
    <div class="row justify-content-center">
    <div class="container">
    <div class="row justify-content-center align-items-center">
        <div class="col-md-6">
        
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="mb-3">
                
                <div class="container">
    <div class="row">
        <!-- Form for sorting -->
        <div class="col-md-6">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="d-flex gap-2">
                    <select class="form-select" aria-label="Sortir Buku" id="sort" name="sort">
                        <option selected value="penulis">Penulis</option>
                        <option value="tahun">Tahun Terbit</option>
                    </select>
                    <button type="submit" name="apply_sort" class="btn btn-success">
                        <i class="fa-solid fa-filter"></i>
                    </button>
                </div>
            </form>
        </div>
        <!-- Form for searching -->
        <div class="col-md-6">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Cari buku" name="keyword" aria-label="Cari buku" aria-describedby="button-addon2">
                    <button class="btn btn-outline-light" type="submit" id="button-addon2">Cari</button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>

            </form>
            </div>
        </div>
    </div>
</div>

    </div>
</div>

</div>




    

        <div class="container">
        <div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card my-3">
            <div class="card-header">
                Menambahkan Buku
            </div>
            <div class="card-body">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="mb-3">
                        <label for="inJudul" class="form-label">Judul Buku</label>
                        <input type="text" class="form-control" id="inJudul" name="judul" required>
                </div>
                <div class="mb-3">
                        <label for="inTahun" class="form-label">Tahun Terbit</label>
                        <input type="number" min="1840" max="2030" step="1" class="form-control" id="inTahun"
                            name="tahun" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="inPenulis" class="form-label">Penulis</label>
                        <input type="text" class="form-control" id="inPenulis" name="penulis" required>
                    </div>
                    <div class="mb-3">
                        <label for="inPenerbit" class="form-label">Penerbit</label>
                        <input type="text" class="form-control" id="inPenerbit" name="penerbit" required>
                    </div>
                    <div class="mb-3">
                        <label for="inISBN" class="form-label">ISBN</label>
                        <input type="text" class="form-control" id="inISBN" name="isbn" required>
                    </div>
                    <button type="submit" name="tambahBuku" class="btn btn-success"><i class="fa-light fa-book-sparkles"></i>Tambahkan</i></button>
                </form>
            </div>
        </div>
    </div>
</div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card my-3">
                <div class="card-header">
                    Mengembalikan buku
                </div>
                <div class="card-body">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                        <label for="kembaliISBN">Judul Buku</label>
                        <select class="form-select mb-3" aria-label="Default select example" name="isbn"
                            id="kembaliISBN" required>
                            <?php
                            $counter = 0;

                            foreach ($_SESSION['perpustakaan']->getSemuaBuku() as $book) {
                                if ($book->dipinjam()) {
                                    echo "<option value='" . $book->getISBN() . "'>" . $book->getJudul() . "</option>";
                                } else {
                                    $counter++;
                                }
                            }
                            if ($counter === sizeof($_SESSION['perpustakaan']->getSemuaBuku())) {
                                echo "<option value='kosong'>Tidak ada buku yang sedang dipinjam</option>";
                            } ?>
                        </select>
                        <button type="submit" name="kembalikanBuku" class="btn btn-success">
                            Kembalikan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Book cards in grid view -->
<div class="row">
        <?php
        foreach ($_SESSION['perpustakaan']->getSemuaBuku() as $book) {
            if (!$book->dipinjam()) {
                echo "<div class='col-md-4'>";
                echo "<div class='card mb-3'>";
                echo "<div class='card-header'>";
                echo $book->getJudul();
                echo "</div>";
                echo "<div class='card-body'>";
                echo "<h6 class='card-subtitle mb-2 text-muted'>" . $book->getPenulis() . " - " . $book->getTahunTerbit() . "</h6>";
                echo "<h6 class='card-subtitle mb-2 text-muted'>" . $book->getPenerbit() . "</h6>";
                echo "<div class='d-flex justify-content-end'>";
                echo "<a type='button' class='btn btn-success btn-pinjam' data-bs-toggle='modal' data-bs-target='#modalPinjam' data-isbn='" . $book->getISBN() . "'><i></i> Pinjam</a>";
                echo "<a type='button' class='btn btn-danger btn-hapus ms-2' data-bs-toggle='modal' data-bs-target='#modalHapus' data-isbn='" . $book->getISBN() . "'><i></i> Hapus</a>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
            }
        }
        ?>
    </div>
</div>

    <script>
        $(document).on("click", ".btn-hapus", function () {
            var isbn = $(this).data('isbn');
            $(".modal-body #hapusISBN").val(isbn);
        });
        $(document).on("click", ".btn-pinjam", function () {
            var isbn = $(this).data('isbn');
            $(".modal-body #pinjamISBN").val(isbn);
        });
    </script>
</body>

</html>