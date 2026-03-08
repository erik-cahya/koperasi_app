<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

class Berjangka extends OperatorController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('fungsi');
        $this->load->model('berjangka_m');
        $this->load->model('general_m');
    }

    public function index()
    {
        $this->data['judul_browser'] = 'Tabungan Berjangka';
        $this->data['judul_utama'] = 'Tabungan Berjangka';
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

        $this->data['kas_id'] = $this->berjangka_m->get_data_kas();

        $this->data['isi'] = $this->load->view('berjangka_list_v', $this->data, TRUE);
        $this->load->view('themes/layout_utama_v', $this->data);
    }

    function list_anggota()
    {
        $q = isset($_POST['q']) ? $_POST['q'] : '';
        $data   = $this->general_m->get_data_anggota_ajax($q);
        $i    = 0;
        $rows   = array();
        foreach ($data['data'] as $r) {
            if ($r->file_pic == '') {
                $rows[$i]['photo'] = '<img src="' . base_url() . 'assets/theme_admin/img/photo.jpg" alt="default" width="30" height="40" />';
            } else {
                $rows[$i]['photo'] = '<img src="' . base_url() . 'uploads/anggota/' . $r->file_pic . '" alt="Foto" width="30" height="40" />';
            }
            $rows[$i]['id'] = $r->id;
            $rows[$i]['kode_anggota'] = 'AG' . sprintf('%04d', $r->id) . '<br>' . $r->identitas;
            $rows[$i]['nama'] = $r->nama;
            $rows[$i]['kota'] = $r->kota . '<br>' . $r->departement;
            $i++;
        }
        //keys total & rows wajib bagi jEasyUI
        $result = array('total' => $data['count'], 'rows' => $rows);
        echo json_encode($result);
    }

    function get_anggota_by_id()
    {
        $id = isset($_POST['anggota_id']) ? $_POST['anggota_id'] : '';
        $r   = $this->general_m->get_data_anggota($id);
        $out = '';
        $photo_w = 3 * 30;
        $photo_h = 4 * 30;
        if ($r->file_pic == '') {
            $out = '<img src="' . base_url() . 'assets/theme_admin/img/photo.jpg" alt="default" width="' . $photo_w . '" height="' . $photo_h . '" />'
                . '<br> ID : ' . 'AG' . sprintf('%04d', $r->id) . '';
        } else {
            $out = '<img src="' . base_url() . 'uploads/anggota/' . $r->file_pic . '" alt="Foto" width="' . $photo_w . '" height="' . $photo_h . '" />'
                . '<br> ID : ' . 'AG' . sprintf('%04d', $r->id) . '';
        }
        echo $out;
        exit();
    }

    function ajax_list()
    {
        $offset = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $limit  = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
        $sort  = isset($_POST['sort']) ? $_POST['sort'] : 'tgl_daftar';
        $order  = isset($_POST['order']) ? $_POST['order'] : 'desc';
        $kode_transaksi = isset($_POST['kode_transaksi']) ? $_POST['kode_transaksi'] : '';
        $cari_status = isset($_POST['cari_status']) ? $_POST['cari_status'] : '';
        $tgl_dari = isset($_POST['tgl_dari']) ? $_POST['tgl_dari'] : '';
        $tgl_sampai = isset($_POST['tgl_sampai']) ? $_POST['tgl_sampai'] : '';

        $search = array(
            'kode_transaksi' => $kode_transaksi,
            'cari_status' => $cari_status,
            'tgl_dari' => $tgl_dari,
            'tgl_sampai' => $tgl_sampai
        );
        $offset = ($offset - 1) * $limit;
        $data   = $this->berjangka_m->get_data_transaksi_ajax($offset, $limit, $search, $sort, $order);
        $i    = 0;
        $rows   = array();

        foreach ($data['data'] as $r) {
            $tgl_bayar = explode(' ', $r->tgl_daftar);
            $txt_tanggal = jin_date_ina($tgl_bayar[0]);
            $txt_tanggal .= ' - ' . substr($tgl_bayar[1], 0, 5);

            $tgl_jatuh = explode(' ', $r->tgl_jatuh_tempo);
            $txt_jatuh_tempo = jin_date_ina($tgl_jatuh[0]);

            $anggota = $this->general_m->get_data_anggota($r->anggota_id);
            $nama_anggota = $anggota ? $anggota->nama : 'Unknown';

            // Bunga total yang didapat dari total terkumpul di akhir masa
            // Di sini bunga diasumsikan bunga (per tahun) diterapkan ke total saldo
            $estimasi_bunga = ($r->bunga / 100 / 12) * $r->lama_bulan * $r->total_terkumpul;
            $total_kembali = $r->total_terkumpul + $estimasi_bunga;

            $rows[$i]['id'] = $r->id;
            $rows[$i]['id_txt'] = 'TRB' . sprintf('%05d', $r->id) . '';
            $rows[$i]['tgl_daftar'] = $r->tgl_daftar;
            $rows[$i]['tgl_daftar_txt'] = $txt_tanggal;
            $rows[$i]['anggota_id'] = $r->anggota_id;
            $rows[$i]['anggota_id_txt'] = $anggota ? $anggota->identitas : 'N/A';
            $rows[$i]['nama'] = '<a href="javascript:void(0)" onclick="show_history_setor(' . $r->id . ', \'' . addslashes($nama_anggota) . '\')" title="Lihat History Setoran" style="font-weight:bold; color:blue;">' . $nama_anggota . '</a>';
            $rows[$i]['departement'] = $anggota ? $anggota->departement : '-';

            $rows[$i]['setoran_per_bulan'] = number_format($r->setoran_per_bulan);
            $rows[$i]['lama_bulan'] = $r->lama_bulan . ' Bulan';
            $rows[$i]['bunga'] = $r->bunga . '%';
            $rows[$i]['tgl_jatuh_tempo_txt'] = $txt_jatuh_tempo;
            $rows[$i]['total_terkumpul'] = number_format($r->total_terkumpul);
            $rows[$i]['estimasi_bunga'] = number_format($estimasi_bunga);
            $rows[$i]['total_kembali'] = number_format($total_kembali);

            if ($r->status == 'Aktif') {
                $rows[$i]['status'] = '<span class="label label-success">Aktif</span>';
            } else {
                $rows[$i]['status'] = '<span class="label label-default">Selesai/Cair</span>';
            }

            $rows[$i]['ket'] = $r->keterangan;
            $rows[$i]['user'] = $r->user_name;
            $rows[$i]['kas_id'] = $r->kas_id;
            $i++;
        }
        $result = array('total' => $data['count'], 'rows' => $rows);
        echo json_encode($result);
    }

    function ajax_list_setor($berjangka_id)
    {
        $offset = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $limit  = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
        $sort  = isset($_POST['sort']) ? $_POST['sort'] : 'tgl_transaksi';
        $order  = isset($_POST['order']) ? $_POST['order'] : 'asc';

        $offset = ($offset - 1) * $limit;
        $data   = $this->berjangka_m->get_data_setor_ajax($offset, $limit, $berjangka_id, $sort, $order);
        $i    = 0;
        $rows   = array();

        foreach ($data['data'] as $r) {
            $tgl_bayar = explode(' ', $r->tgl_transaksi);
            $txt_tanggal = jin_date_ina($tgl_bayar[0]);
            $txt_tanggal .= ' - ' . substr($tgl_bayar[1], 0, 5);

            $rows[$i]['id'] = $r->id;
            $rows[$i]['tgl_transaksi'] = $r->tgl_transaksi;
            $rows[$i]['tgl_transaksi_txt'] = $txt_tanggal;
            $rows[$i]['jumlah'] = number_format($r->jumlah);
            $rows[$i]['keterangan'] = $r->keterangan;
            $rows[$i]['user_name'] = $r->user_name;
            $i++;
        }
        $result = array('total' => $data['count'], 'rows' => $rows);
        echo json_encode($result);
    }

    public function create()
    {
        if (!isset($_POST)) {
            show_404();
        }
        if ($this->berjangka_m->create()) {
            echo json_encode(array('ok' => true, 'msg' => '<div class="text-green"><i class="fa fa-check"></i> Kontrak Berjangka berhasil disimpan </div>'));
        } else {
            echo json_encode(array('ok' => false, 'msg' => '<div class="text-red"><i class="fa fa-ban"></i> Gagal menyimpan data, pastikan input form terisi benar. </div>'));
        }
    }

    public function setor()
    {
        if (!isset($_POST)) {
            show_404();
        }
        if ($this->berjangka_m->setor()) {
            echo json_encode(array('ok' => true, 'msg' => '<div class="text-green"><i class="fa fa-check"></i> Setoran berhasil disimpan </div>'));
        } else {
            echo json_encode(array('ok' => false, 'msg' => '<div class="text-red"><i class="fa fa-ban"></i> Gagal menyimpan setoran, pastikan input form terisi benar. </div>'));
        }
    }

    public function pencairan()
    {
        if (!isset($_POST)) {
            show_404();
        }
        $id = intval(addslashes($_POST['id']));
        if ($this->berjangka_m->pencairan($id)) {
            echo json_encode(array('ok' => true, 'msg' => '<div class="text-green"><i class="fa fa-check"></i> Pencairan Tabungan Berjangka berhasil </div>'));
        } else {
            echo json_encode(array('ok' => false, 'msg' => '<div class="text-red"><i class="fa fa-ban"></i>  Maaf, Pencairan gagal </div>'));
        }
    }

    public function delete()
    {
        if (!isset($_POST)) {
            show_404();
        }
        $id = intval(addslashes($_POST['id']));
        if ($this->berjangka_m->delete($id)) {
            echo json_encode(array('ok' => true, 'msg' => '<div class="text-green"><i class="fa fa-check"></i> Data berhasil dihapus </div>'));
        } else {
            echo json_encode(array('ok' => false, 'msg' => '<div class="text-red"><i class="fa fa-ban"></i> Maaf, Data gagal dihapus </div>'));
        }
    }
}
