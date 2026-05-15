<!-- Styler -->
<style type="text/css">
.panel * {
	font-family: "Arial","​Helvetica","​sans-serif";
}
.fa {
	font-family: "FontAwesome";
}
.datagrid-header-row * {
	font-weight: bold;
}
.messager-window * a:focus, .messager-window * span:focus {
	color: blue;
	font-weight: bold;
}
.daterangepicker * {
	font-family: "Source Sans Pro","Arial","​Helvetica","​sans-serif";
	box-sizing: border-box;
}
.glyphicon	{font-family: "Glyphicons Halflings"}

.form-control {
	height: 20px;
	padding: 4px;
}	
</style>

<?php 
	if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
		$tgl_dari = $_REQUEST['tgl_dari'];
		$tgl_samp = $_REQUEST['tgl_samp'];
	} else {
		$tgl_dari = date('Y') . '-01-01';
		$tgl_samp = date('Y') . '-12-31';
	}
	$tgl_dari_txt = jin_date_ina($tgl_dari, 'p');
	$tgl_samp_txt = jin_date_ina($tgl_samp, 'p');
	$tgl_periode_txt = $tgl_dari_txt . ' - ' . $tgl_samp_txt;
?>

<div class="box box-solid box-primary">
	<div class="box-header">
		<h3 class="box-title">Cetak Data Deposito</h3>
		<div class="box-tools pull-right">
			<button class="btn btn-primary btn-sm" data-widget="collapse">
				<i class="fa fa-minus"></i>
			</button>
		</div>
	</div>
	<div class="box-body">
		<div>
			<form id="fmCari" method="GET">
				<input type="hidden" name="tgl_dari" id="tgl_dari">
				<input type="hidden" name="tgl_samp" id="tgl_samp">
				<table>
					<tr>
						<td>
							<div id="filter_tgl" class="input-group" style="display: inline;">
								<button class="btn btn-default" id="daterange-btn">
									<i class="fa fa-calendar"></i> <span id="reportrange"><span><?php echo $tgl_periode_txt; ?>
									</span></span>
									<i class="fa fa-caret-down"></i>
								</button>
							</div>
						</td>
						<td>

							<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-print" plain="false" onclick="cetak()">Cetak Laporan</a>

							<a href="javascript:void(0);" class="easyui-linkbutton" iconCls="icon-clear" plain="false" onclick="clearSearch()">Hapus Filter</a>
						</td>
					</tr>
				</table>
			</form>
		</div>
	</div>
</div>

<div class="box box-primary">
<div class="box-body">
<p></p>
	<p style="text-align:center; font-size: 15pt; font-weight: bold;"> Laporan Deposito Periode <?php echo $tgl_periode_txt; ?></p>
	<table  class="table table-bordered">
		<tr class="header_kolom">
			<th class="h_tengah" style="width:5%; vertical-align: middle " > No. </th>
			<th class="h_tengah" style="width:8%; vertical-align: middle"> Nomor <br>Kontrak</th>
			<th class="h_tengah" style="width:8%; vertical-align: middle"> Tanggal <br>Deposito</th>
			<th class="h_tengah" style="width:18%; vertical-align: middle"> Nama Anggota </th>
			<th class="h_tengah" style="width:5%; vertical-align: middle"> Lama <br>(Bulan)  </th>
			<th class="h_tengah" style="width:5%; vertical-align: middle"> Bunga (%) </th>
			<th class="h_tengah" style="width:8%; vertical-align: middle"> Jatuh Tempo </th>
			<th class="h_tengah" style="width:12%; vertical-align: middle"> Jumlah Deposito </th>
			<th class="h_tengah" style="width:12%; vertical-align: middle"> Sisa Bunga </th>
			<th class="h_tengah" style="width:13%; vertical-align: middle"> Total Pencairan </th>
			<th class="h_tengah" style="width:6%; vertical-align: middle"> Status  </th>
		</tr>

	<?php 
	$no = $offset + 1;
	$total = 0;
	$total_bunga = 0;
	$total_pencairan = 0;
	if (!empty($data_deposito)) {
		foreach ($data_deposito as $row) {
			if(($no % 2) == 0) {
				$warna="#EEEEEE"; } 
			else {
				$warna="#FFFFFF"; }

			$tgl = explode(' ', $row->tgl_deposito);
			$txt_tanggal = jin_date_ina($tgl[0],'p');

			$tgl_j = explode(' ', $row->tgl_jatuh_tempo);
			$txt_jatuh_tempo = jin_date_ina($tgl_j[0],'p');
			
			$anggota = $this->lap_deposito_m->get_data_anggota($row->anggota_id);
			$nama_anggota = $anggota ? $anggota->nama : 'N/A';
			
			$estimasi_bunga_asli = ($row->bunga / 100 / 12) * $row->lama_bulan * $row->jumlah;

			$CI =& get_instance();
			$CI->db->select_sum('jumlah');
			$CI->db->where('deposito_id', $row->id);
			$q_pencairan = $CI->db->get('tbl_deposito_bunga')->row();
			$total_dicairkan = $q_pencairan->jumlah ? $q_pencairan->jumlah : 0;

			$estimasi_bunga = $estimasi_bunga_asli - $total_dicairkan;
			$total_kembali = $row->jumlah + $estimasi_bunga;

			$total += $row->jumlah;
			$total_bunga += $estimasi_bunga;
			$total_pencairan += $total_kembali;

			if ($row->status == 'Aktif') {
				$status = '<span class="label label-success">Aktif</span>';
			} else {
				$status = '<span class="label label-default">Cair</span>';
			}

			echo '
				<tr bgcolor='.$warna.'>
						<td class="h_tengah" style="vertical-align:middle">'.$no++.'</td>
						<td class="h_tengah" style="vertical-align:middle"> TRD'.sprintf('%05d', $row->id).'</td>
						<td class="h_tengah" style="vertical-align:middle"> '.$txt_tanggal.'</td>
						<td class="h_kiri" style="vertical-align:middle"> <b>'.$nama_anggota.'</b></td>
						<td class="h_tengah" style="vertical-align:middle"> '.$row->lama_bulan.'</td>
						<td class="h_tengah" style="vertical-align:middle"> '.$row->bunga.' </td>
						<td class="h_tengah" style="vertical-align:middle"> '.$txt_jatuh_tempo.'</td>
						<td class="h_kanan" style="vertical-align:middle"> <b>'.number_format($row->jumlah).'</b></td>
						<td class="h_kanan" style="vertical-align:middle"> <b>'.number_format($estimasi_bunga).'</b></td>
						<td class="h_kanan" style="vertical-align:middle"> <b>'.number_format($total_kembali).'</b></td>
						<td class="h_tengah" style="vertical-align:middle"> '.$status.' </td>
				</tr>';
		}
		echo '
		<tr bgcolor="#FFFFEE">
			<td colspan="7" class="h_kanan"><strong>TOTAL</strong></td>
			<td class="h_kanan"><strong>'.number_format($total).'</strong></td>
			<td class="h_kanan"><strong>'.number_format($total_bunga).'</strong></td>
			<td class="h_kanan"><strong>'.number_format($total_pencairan).'</strong></td>
			<td></td>
		</tr>';
		echo '</table>
		<div class="box-footer">'.$halaman.'</div>';
	} else {
		echo '<tr>
			<td colspan="9" >
				<code> Tidak Ada Data <br> </code>
			</td>
		</tr>';
		echo '</table>';
	}
?>
</div>
</div>


<script type="text/javascript">
$(document).ready(function() {
	fm_filter_tgl();
}); // ready

function fm_filter_tgl() {
	$('#daterange-btn').daterangepicker({
		ranges: {
			'Hari ini': [moment(), moment()],
			'Kemarin': [moment().subtract('days', 1), moment().subtract('days', 1)],
			'7 Hari yang lalu': [moment().subtract('days', 6), moment()],
			'30 Hari yang lalu': [moment().subtract('days', 29), moment()],
			'Bulan ini': [moment().startOf('month'), moment().endOf('month')],
			'Bulan kemarin': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')],
			'Tahun ini': [moment().startOf('year').startOf('month'), moment().endOf('year').endOf('month')],
			'Tahun kemarin': [moment().subtract('year', 1).startOf('year').startOf('month'), moment().subtract('year', 1).endOf('year').endOf('month')]
		},
		locale: 'id',
		showDropdowns: true,
		format: 'YYYY-MM-DD',
		<?php 
			if(isset($tgl_dari) && isset($tgl_samp)) {
				echo "
					startDate: '".$tgl_dari."',
					endDate: '".$tgl_samp."'
				";
			} else {
				echo "
					startDate: moment().startOf('year').startOf('month'),
					endDate: moment().endOf('year').endOf('month')
				";
			}
		?>
	},

	function (start, end) {
		doSearch();
	});
}

function clearSearch(){
	window.location.href = '<?php echo site_url("lap_deposito"); ?>';
}

function doSearch() {
	var tgl_dari = $('input[name=daterangepicker_start]').val();
	var tgl_samp = $('input[name=daterangepicker_end]').val();
	$('input[name=tgl_dari]').val(tgl_dari);
	$('input[name=tgl_samp]').val(tgl_samp);
	$('#fmCari').attr('action', '<?php echo site_url('lap_deposito'); ?>');
	$('#fmCari').submit();	
}

function cetak () {
	var tgl_dari = $('input[name=daterangepicker_start]').val();
	var tgl_samp = $('input[name=daterangepicker_end]').val();
	
    var url = '<?php echo site_url("lap_deposito/cetak"); ?>';
    if(tgl_dari && tgl_samp) {
        url += '?tgl_dari=' + tgl_dari + '&tgl_samp=' + tgl_samp;
    }

	var win = window.open(url);
	if (win) {
		win.focus();
	} else {
		alert('Popup jangan di block');
	}

}
</script>
