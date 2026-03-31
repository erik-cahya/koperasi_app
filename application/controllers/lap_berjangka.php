<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Lap_berjangka extends OperatorController {

	public function __construct() {
		parent::__construct();	
		$this->load->helper('fungsi');
		$this->load->model('lap_berjangka_m');
		$this->load->model('general_m');
	}	

	public function index() {
		$this->load->library("pagination");

		$this->data['judul_browser'] = 'Laporan';
		$this->data['judul_utama'] = 'Laporan';
		$this->data['judul_sub'] = 'Data Tabungan Berjangka';

		$this->data['css_files'][] = base_url() . 'assets/easyui/themes/default/easyui.css';
		$this->data['css_files'][] = base_url() . 'assets/easyui/themes/icon.css';
		$this->data['js_files'][] = base_url() . 'assets/easyui/jquery.easyui.min.js';

		#include tanggal
		$this->data['css_files'][] = base_url() . 'assets/extra/bootstrap_date_time/css/bootstrap-datetimepicker.min.css';
		$this->data['js_files'][] = base_url() . 'assets/extra/bootstrap_date_time/js/bootstrap-datetimepicker.min.js';
		$this->data['js_files'][] = base_url() . 'assets/extra/bootstrap_date_time/js/locales/bootstrap-datetimepicker.id.js';

		#include daterange
		$this->data['css_files'][] = base_url() . 'assets/theme_admin/css/daterangepicker/daterangepicker-bs3.css';
		$this->data['js_files'][] = base_url() . 'assets/theme_admin/js/plugins/daterangepicker/daterangepicker.js';

		//number_format
		$this->data['js_files'][] = base_url() . 'assets/extra/fungsi/number_format.js';

		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			//
		} else {
			$_GET['tgl_dari'] = date('Y') . '-01-01';
			$_GET['tgl_samp'] = date('Y') . '-12-31';
		}

		$config = array();
		$config["base_url"] = base_url() . "lap_berjangka/index/halaman";
		if (count($_GET) > 0) $config['suffix'] = '?' . http_build_query($_GET, '', "&");
		$config['first_url'] = $config['base_url'].'?'.http_build_query($_GET);
		$config["total_rows"] = $this->lap_berjangka_m->get_jml_data_berjangka();
		$config["per_page"] = 20;
		$config["uri_segment"] = 4;
		$config['num_links'] = 10;
		$config['use_page_numbers'] = TRUE;

		$config['full_tag_open'] = '<ul class="pagination">';
		$config['full_tag_close'] = '</ul>';

		$config['first_link'] = '&laquo; First';
		$config['first_tag_open'] = '<li class="prev page">';
		$config['first_tag_close'] = '</li>';

		$config['last_link'] = 'Last &raquo;';
		$config['last_tag_open'] = '<li class="next page">';
		$config['last_tag_close'] = '</li>';

		$config['next_link'] = 'Next &rarr;';
		$config['next_tag_open'] = '<li class="next page">';
		$config['next_tag_close'] = '</li>';

		$config['prev_link'] = '&larr; Previous';
		$config['prev_tag_open'] = '<li class="prev page">';
		$config['prev_tag_close'] = '</li>';

		$config['cur_tag_open'] = '<li class="active"><a href="">';
		$config['cur_tag_close'] = '</a></li>';

		$config['num_tag_open'] = '<li class="page">';
		$config['num_tag_close'] = '</li>';

		$this->pagination->initialize($config);
		$offset = ($this->uri->segment(4)) ? $this->uri->segment(4) : 0;
		if($offset > 0) {
			$offset = ($offset * $config['per_page']) - $config['per_page'];
		}
		
		$this->data["data_berjangka"] = $this->lap_berjangka_m->get_data_berjangka($config["per_page"], $offset);
		$this->data["halaman"] = $this->pagination->create_links();
		$this->data["offset"] = $offset;

		$this->data['isi'] = $this->load->view('lap_berjangka_list_v', $this->data, TRUE);
		$this->load->view('themes/layout_utama_v', $this->data);
	}

	function cetak() {
		$data_berjangka = $this->lap_berjangka_m->lap_data_berjangka();
		if($data_berjangka == FALSE) {
			echo 'DATA KOSONG';
			exit();
		}

		$tgl_dari = $_REQUEST['tgl_dari'];
		$tgl_samp = $_REQUEST['tgl_samp'];
		$tgl_dari_txt = jin_date_ina($tgl_dari, 'p');
		$tgl_samp_txt = jin_date_ina($tgl_samp, 'p');
		$tgl_periode_txt = $tgl_dari_txt . ' - ' . $tgl_samp_txt;

		$this->load->library('Pdf');
		$pdf = new Pdf('L', 'mm', 'A4', true, 'UTF-8', false);
		$pdf->set_nsi_header(TRUE);
		$pdf->AddPage('L');

		$html = '
		<style>
			.h_tengah {text-align: center;}
			.h_kiri {text-align: left;}
			.h_kanan {text-align: right;}
			.txt_judul {font-size: 12pt; font-weight: bold; padding-bottom: 15px;}
			.header_kolom {background-color: #cccccc; text-align: center; font-weight: bold;}
		</style>
		'.$pdf->nsi_box($text = '<span class="txt_judul">Laporan Tabungan Berjangka Periode '.$tgl_periode_txt.'</span>', $width = '100%', $spacing = '1', $padding = '1', $border = '0', $align = 'center').'';
		
		$html.='<table cellspacing="0" cellpadding="3" border="1" nobr="true">
		<tr class="header_kolom">
			<th class="h_tengah" style="width:4%;" > No. </th>
			<th class="h_tengah" style="width:8%;"> Nomor <br>Kontrak</th>
			<th class="h_tengah" style="width:10%;"> Tanggal <br>Daftar</th>
			<th class="h_tengah" style="width:18%;"> Nama <br>Anggota </th>
			<th class="h_tengah" style="width:10%;"> Lama Bulan </th>
			<th class="h_tengah" style="width:10%;"> Bunga (%) </th>
			<th class="h_tengah" style="width:10%;"> Jatuh Tempo </th>
			<th class="h_tengah" style="width:15%;"> Total Terkumpul </th>
			<th class="h_tengah" style="width:15%;"> Status  </th>
		</tr>';

		$no = 1;
		$total_terkumpul = 0;
		foreach ($data_berjangka as $row) {
			$tgl = explode(' ', $row->tgl_daftar);
			$txt_tanggal = jin_date_ina($tgl[0],'p');
			
			$tgl_j = explode(' ', $row->tgl_jatuh_tempo);
			$txt_jatuh_tempo = jin_date_ina($tgl_j[0],'p');

			$anggota = $this->lap_berjangka_m->get_data_anggota($row->anggota_id);
			$nama_anggota = $anggota ? $anggota->nama : 'N/A';
			$total_terkumpul += $row->total_terkumpul;

			$html .= '
			<tr>
				<td class="h_tengah"> '.$no++.'</td>
				<td class="h_tengah"> TRB'.sprintf('%05d', $row->id).'</td>
				<td class="h_tengah"> '.$txt_tanggal.'</td>
				<td class="h_kiri"> '.$nama_anggota.'</td>
				<td class="h_tengah"> '.$row->lama_bulan.'</td>
				<td class="h_tengah"> '.$row->bunga.'</td>
				<td class="h_tengah"> '.$txt_jatuh_tempo.'</td>
				<td class="h_kanan"> '.number_format($row->total_terkumpul).'</td>
				<td class="h_tengah"> '.$row->status.'</td>
			</tr>';
		}
		$html.='
		<tr>
			<td colspan="7" class="h_kanan"><strong>TOTAL TERKUMPUL</strong></td>
			<td class="h_kanan"><strong>'.number_format($total_terkumpul).'</strong></td>
			<td></td>
		</tr>
		</table>';

		$pdf->nsi_html($html);
		$pdf->Output('lap_berjangka'.date('Ymd_His') . '.pdf', 'I');
	} 
}
