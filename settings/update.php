<?php
if(!isset($_SESSION["mikhmon"])){
    header("Location:../admin.php?id=login");
}

require_once(__DIR__ . '/../database/database.php');

// Handle form save for GitHub URL
if (isset($_POST['save_repo_url'])) {
    $repo_url = filter_var($_POST['github_url'], FILTER_SANITIZE_URL);
    Database::setSetting('github_repo_url', $repo_url);
    $success_msg = "Pengaturan Repositori GitHub berhasil disimpan.";
}

// Get current repo url
$current_repo_url = Database::getSetting('github_repo_url', 'https://github.com/username/repo/archive/refs/heads/main.zip');

?>

<div class="row">
    <div class="col-8">
        <div class="card">
            <div class="card-header">
                <h3><i class="fa fa-cloud-download"></i> Sistem Auto-Update (GitHub)</h3>
            </div>
            <div class="card-body">
                <p>Fitur ini memungkinkan Anda untuk mengunduh dan menerapkan update terbaru untuk aplikasi Mikhmon langsung dari repositori GitHub secara otomatis.</p>
                <div class="bg-warning pd-10 radius-3 mb-3">
                    <i class="fa fa-warning"></i> <b>Peringatan:</b> Proses update akan menimpa seluruh file sistem. Folder <b>database/</b> dan konfigurasi router Anda secara fungsional aman karena SQLite atau DB lainnya memisah data. Namun pastikan ekstensi zip server menyala.
                </div>
                
                <?php if (isset($success_msg)) echo '<div class="bg-success pd-10 radius-3 mb-3">'.$success_msg.'</div>'; ?>

                <form method="post" action="./admin.php?id=update">
                    <div class="form-group row">
                        <div class="col-3"><label>URL Repositori GitHub (.zip)</label></div>
                        <div class="col-9">
                            <input type="url" name="github_url" class="form-control" value="<?= htmlspecialchars($current_repo_url) ?>" placeholder="https://github.com/user/repo/archive/refs/heads/main.zip" required>
                            <small class="text-muted">Pastikan URL berakhiran <b>.zip</b> yang mengarah ke file ZIP source code.</small>
                        </div>
                    </div>
                    <div class="form-group row mt-2">
                        <div class="col-3"></div>
                        <div class="col-9">
                            <button type="submit" name="save_repo_url" class="btn bg-primary"><i class="fa fa-save"></i> Simpan URL Repositori</button>
                        </div>
                    </div>
                </form>

                <hr class="mt-4 mb-4">
                
                <h4>Mulai Proses Update</h4>
                <p>Silakan klik tombol di bawah ini untuk memulai proses pengunduhan dan penimpaan file.</p>
                
                <button type="button" id="btn-start-update" class="btn bg-success text-bold" style="padding: 10px 20px; font-size: 16px;">
                    <i class="fa fa-download"></i> Install Update Sekarang
                </button>

                <div id="update-log-container" class="mt-4" style="display: none;">
                    <h5><i class="fa fa-terminal"></i> Log Proses Update</h5>
                    <div id="update-log" style="background: #1e1e1e; color: #00ff00; padding: 15px; font-family: monospace; height: 300px; overflow-y: scroll; border-radius: 5px; line-height: 1.5;">
                        <!-- Log lines will be appended here -->
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#btn-start-update').click(function() {
        if (!confirm("Apakah Anda yakin ingin memulai proses update sekarang? Aplikasi mungkin tidak bisa diakses selama proses pengekstrakkan.")) {
            return;
        }

        $(this).attr('disabled', true).html('<i class="fa fa-circle-o-notch fa-spin"></i> Menyiapkan Update...');
        $('#update-log-container').slideDown();
        const $log = $('#update-log');
        $log.empty();

        function addLog(message, isError = false) {
            const time = new Date().toLocaleTimeString();
            const color = isError ? '#ff4d4d' : '#00ff00';
            $log.append(`<div style="color: ${color}">[${time}] ${message}</div>`);
            $log.scrollTop($log[0].scrollHeight);
        }

        addLog("Memulai sistem update...");
        addLog("Menghubungi server pembaruan...");

        $.ajax({
            url: './process/pupdate.php',
            type: 'POST',
            data: { action: 'start_update' },
            dataType: 'json',
            success: function(response) {
                if (response.logs && response.logs.length > 0) {
                    response.logs.forEach(msg => addLog(msg));
                }

                if (response.success) {
                    addLog("UPDATE SELESAI!", false);
                    addLog("Halaman akan dimuat ulang dalam 3 detik...");
                    $('#btn-start-update').html('<i class="fa fa-check"></i> Selesai');
                    setTimeout(() => {
                        window.location.reload();
                    }, 3000);
                } else {
                    addLog("ERROR: " + response.error, true);
                    $('#btn-start-update').attr('disabled', false).html('<i class="fa fa-warning"></i> Coba Lagi');
                }
            },
            error: function(xhr, status, error) {
                addLog("Terjadi kesalahan koneksi saat menjalankan pupdate.php.", true);
                addLog(error, true);
                $('#btn-start-update').attr('disabled', false).html('<i class="fa fa-warning"></i> Coba Lagi');
            }
        });
    });
});
</script>
