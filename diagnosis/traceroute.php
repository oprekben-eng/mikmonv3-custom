<?php
if (!isset($_SESSION["mikhmon"])) {
  header("Location:../admin.php?id=login");
} else {
    // Meminta list client aktif pppoe dari routerOS untuk auto-fill
    $ppp_active = $API->comm("/ppp/active/print");
?>
<div class="row">
  <div class="col-8">
    <div class="card">
      <div class="card-header">
        <h3><i class="fa fa-map-signs"></i> Traceroute Jaringan</h3>
      </div>
      <div class="card-body">
        <label>Alamat IP / Hostname Tujuan:</label>
        <div class="input-group">
            <input type="text" class="form-control" id="trace_target" value="8.8.8.8" placeholder="Contoh: google.com">
            <button class="btn btn-primary bg-primary" id="btn_trace" onclick="runDiagnosis('traceroute')">
                <i class="fa fa-share text-white"></i> <span class="text-white">Lacak Rute (Hops)</span>
            </button>
        </div>
      </div>
    </div>
  </div>
  <div class="col-4">
    <div class="card">
      <div class="card-header">
        <h3><i class="fa fa-users"></i> Pintasan IP PPPoE</h3>
      </div>
      <div class="card-body" style="height: 153px; overflow-y: auto; padding:0;">
        <table class="table table-hover table-sm" style="margin:0;">
          <tbody>
            <?php
            if (count($ppp_active) > 0) {
                foreach ($ppp_active as $pa) {
                    $ip = isset($pa['address']) ? $pa['address'] : '';
                    if($ip) {
                        echo "<tr>";
                        echo "<td style='vertical-align:middle; padding-left:15px;'><i class='fa fa-user mr-1 text-muted'></i> ".$pa['name']."</td>";
                        echo "<td class='text-right'><button class='btn btn-sm bg-warning text-dark' style='padding:2px 7px; font-size:12px; border:none; border-radius:3px; margin-right:10px;' onclick='setTarget(\"".$ip."\")'><b>".$ip."</b></button></td>";
                        echo "</tr>";
                    }
                }
            } else {
                echo "<tr><td colspan='2' class='text-center text-muted' style='padding:20px 0;'>Tidak ada PPP Aktif</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-12">
    <div class="card">
       <div class="card-header">
           <i class="fa fa-terminal"></i> Output Terminal
       </div>
       <div class="card-body" style="padding: 0;">
           <pre id="diag_output" style="background:#1e1e1e; color:#0fce0f; padding:15px; border-radius:0 0 5px 5px; height:350px; overflow-y:auto; font-family: 'Consolas', monospace; font-size:14px; margin:0;"><em>Menunggu perintah...</em></pre>
       </div>
    </div>
  </div>
</div>

<script>
function setTarget(ip) {
    document.getElementById("trace_target").value = ip;
}

function runDiagnosis(type) {
    var target = document.getElementById("trace_target").value;
    if(target == "") {
        alert("IP / Domain Tujuan tidak boleh kosong!");
        return;
    }
    
    document.getElementById("diag_output").innerHTML = "[admin@MikroTik] > traceroute " + target + "\nMelacak rute ke " + target + "...\nProses ini mungkin memerlukan waktu sekitar 15-30 detik...";
    document.getElementById("btn_trace").disabled = true;
    
    $.ajax({
       url: "./process/pdiagnosis.php?session=<?= $session; ?>",
       type: "POST",
       data: {
           type: type,
           target: target
       },
       success: function(response){
           document.getElementById("diag_output").innerHTML = response;
           document.getElementById("btn_trace").disabled = false;
       },
       error: function(){
           document.getElementById("diag_output").innerHTML += "\n<span style='color:#f44336;'>Gagal terhubung ke modul backend.</span>";
           document.getElementById("btn_trace").disabled = false;
       }
    });
}
</script>
<?php } ?>
