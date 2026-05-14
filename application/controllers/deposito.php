<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

class Deposito extends OperatorController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('fungsi');
        $this->load->model('deposito_m');
        $this->load->model('general_m');
    }

    public function index()
    {
        $this->data['judul_browser'] = 'Deposito';
        $this->data['judul_utama'] = 'Deposito';
        $this->data['judul_sub'] = 'Data Tabungan Deposito';

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

        $this->data['kas_id'] = $this->deposito_m->get_data_kas();

        $this->data['isi'] = $this->load->view('deposito_list_v', $this->data, TRUE);
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
        echo json_encode($result); //return nya json
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
        /*Default request pager params dari jeasyUI*/
        $offset = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $limit  = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
        $sort  = isset($_POST['sort']) ? $_POST['sort'] : 'tgl_deposito';
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
        $data   = $this->deposito_m->get_data_transaksi_ajax($offset, $limit, $search, $sort, $order);
        $i    = 0;
        $rows   = array();

        foreach ($data['data'] as $r) {
            $tgl_bayar = explode(' ', $r->tgl_deposito);
            $txt_tanggal = jin_date_ina($tgl_bayar[0]);
            $txt_tanggal .= ' - ' . substr($tgl_bayar[1], 0, 5);

            $tgl_jatuh = explode(' ', $r->tgl_jatuh_tempo);
            $txt_jatuh_tempo = jin_date_ina($tgl_jatuh[0]);

            //array keys ini = attribute 'field' di view nya
            $anggota = $this->general_m->get_data_anggota($r->anggota_id);

            // Kalkulasi Estimasi Bunga (bunga per tahun / 12 * lama bulan * jumlah)
            $estimasi_bunga_asli = ($r->bunga / 100 / 12) * $r->lama_bulan * $r->jumlah;

            $this->db->select_sum('jumlah');
            $this->db->where('deposito_id', $r->id);
            $q_pencairan = $this->db->get('tbl_deposito_bunga')->row();
            $total_dicairkan = $q_pencairan->jumlah ? $q_pencairan->jumlah : 0;

            $estimasi_bunga = $estimasi_bunga_asli - $total_dicairkan;
            $total_kembali = $r->jumlah + $estimasi_bunga;

            $rows[$i]['id'] = $r->id;
            $rows[$i]['id_txt'] = 'TRD' . sprintf('%05d', $r->id) . '';
            $rows[$i]['tgl_deposito'] = $r->tgl_deposito;
            $rows[$i]['tgl_deposito_txt'] = $txt_tanggal;
            $rows[$i]['anggota_id'] = $r->anggota_id;
            $rows[$i]['anggota_id_txt'] = $anggota ? $anggota->identitas : 'N/A';
            $rows[$i]['nama'] = $anggota ? $anggota->nama : 'Unknown';
            $rows[$i]['departement'] = $anggota ? $anggota->departement : '-';
            $rows[$i]['jumlah'] = number_format($r->jumlah);
            $rows[$i]['lama_bulan'] = $r->lama_bulan . ' Bulan';
            $rows[$i]['bunga'] = $r->bunga . '%';
            $rows[$i]['tgl_jatuh_tempo_txt'] = $txt_jatuh_tempo;
            $rows[$i]['estimasi_bunga'] = number_format($estimasi_bunga);
            $rows[$i]['total_kembali'] = number_format($total_kembali);

            // Label Status
            if ($r->status == 'Aktif') {
                $rows[$i]['status'] = '<span class="label label-success">Aktif</span>';
            } else {
                $rows[$i]['status'] = '<span class="label label-default">Cair</span>';
            }

            $rows[$i]['ket'] = $r->keterangan;
            $rows[$i]['user'] = $r->user_name;
            $rows[$i]['kas_id'] = $r->kas_id;
            $i++;
        }
        //keys total & rows wajib bagi jEasyUI
        $result = array('total' => $data['count'], 'rows' => $rows);
        echo json_encode($result); //return nya json
    }

    public function create()
    {
        if (!isset($_POST)) {
            show_404();
        }
        if ($this->deposito_m->create()) {
            echo json_encode(array('ok' => true, 'msg' => '<div class="text-green"><i class="fa fa-check"></i> Data berhasil disimpan </div>'));
        } else {
            echo json_encode(array('ok' => false, 'msg' => '<div class="text-red"><i class="fa fa-ban"></i> Gagal menyimpan data, pastikan input form terisi benar. </div>'));
        }
    }

    public function pencairan()
    {
        if (!isset($_POST)) {
            show_404();
        }
        $id = intval(addslashes($_POST['id']));
        if ($this->deposito_m->pencairan($id)) {
            echo json_encode(array('ok' => true, 'msg' => '<div class="text-green"><i class="fa fa-check"></i> Pencairan Deposito berhasil </div>'));
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
        if ($this->deposito_m->delete($id)) {
            echo json_encode(array('ok' => true, 'msg' => '<div class="text-green"><i class="fa fa-check"></i> Data berhasil dihapus </div>'));
        } else {
            echo json_encode(array('ok' => false, 'msg' => '<div class="text-red"><i class="fa fa-ban"></i> Maaf, Data gagal dihapus </div>'));
        }
    }

    function ajax_list_bunga()
    {
        $deposito_id = isset($_POST['deposito_id']) ? $_POST['deposito_id'] : '';
        $offset = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $limit  = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
        $sort  = isset($_POST['sort']) ? $_POST['sort'] : 'tgl_pencairan';
        $order  = isset($_POST['order']) ? $_POST['order'] : 'desc';

        $offset = ($offset - 1) * $limit;
        $data   = $this->deposito_m->get_data_bunga_ajax($offset, $limit, $deposito_id, $sort, $order);
        $i    = 0;
        $rows   = array();

        foreach ($data['data'] as $r) {
            $tgl = explode(' ', $r->tgl_pencairan);
            $txt_tanggal = jin_date_ina($tgl[0]) . ' - ' . substr($tgl[1], 0, 5);

            $rows[$i]['id'] = $r->id;
            $rows[$i]['tgl_pencairan_txt'] = $txt_tanggal;
            $rows[$i]['jumlah'] = number_format($r->jumlah);
            $rows[$i]['metode'] = $r->metode;
            $rows[$i]['keterangan'] = $r->keterangan;
            $rows[$i]['user_name'] = $r->user_name;
            $i++;
        }
        $result = array('total' => $data['count'], 'rows' => $rows);
        echo json_encode($result);
    }

    public function create_bunga()
    {
        if (!isset($_POST)) {
            show_404();
        }
        $id = intval($this->input->post('deposito_id_bunga'));
        $jumlah_ajuan = str_replace(',', '', $this->input->post('jumlah_bunga'));

        $deposito = $this->db->get_where('tbl_deposito', array('id' => $id))->row();
        if ($deposito) {
            $tgl_deposito = new DateTime($deposito->tgl_deposito);
            $tgl_sekarang = new DateTime();
            $diff = $tgl_deposito->diff($tgl_sekarang);
            $months_passed = ($diff->format('%y') * 12) + $diff->format('%m');

            if ($months_passed > $deposito->lama_bulan) {
                $months_passed = $deposito->lama_bulan;
            }
            $bunga_per_bulan = ($deposito->bunga / 100 / 12) * $deposito->jumlah;
            $total_bunga_didapat = $months_passed * $bunga_per_bulan;

            $this->db->select_sum('jumlah');
            $this->db->where('deposito_id', $id);
            $q_pencairan = $this->db->get('tbl_deposito_bunga')->row();
            $total_dicairkan = $q_pencairan->jumlah ? $q_pencairan->jumlah : 0;

            $bunga_tersedia = $total_bunga_didapat - $total_dicairkan;

            if ($jumlah_ajuan > ($bunga_tersedia + 1)) {
                echo json_encode(array('ok' => false, 'msg' => '<div class="text-red"><i class="fa fa-ban"></i> Pencairan (' . number_format($jumlah_ajuan) . ') melebihi bunga yang tersedia (' . number_format($bunga_tersedia) . ').<br>Bunga didapat sampai saat ini: ' . $months_passed . ' Bulan.</div>'));
                return;
            }
        }

        if ($this->deposito_m->create_bunga()) {
            echo json_encode(array('ok' => true, 'msg' => '<div class="text-green"><i class="fa fa-check"></i> Pencairan bunga berhasil disimpan </div>'));
        } else {
            echo json_encode(array('ok' => false, 'msg' => '<div class="text-red"><i class="fa fa-ban"></i> Gagal menyimpan data pencairan bunga. </div>'));
        }
    }

    public function get_bunga_summary()
    {
        $id = intval($this->input->post('id'));
        $deposito = $this->db->get_where('tbl_deposito', array('id' => $id))->row();
        if ($deposito) {
            $tgl_deposito = new DateTime($deposito->tgl_deposito);
            $tgl_sekarang = new DateTime();
            $diff = $tgl_deposito->diff($tgl_sekarang);
            $months_passed = ($diff->format('%y') * 12) + $diff->format('%m');

            if ($months_passed > $deposito->lama_bulan) {
                $months_passed = $deposito->lama_bulan;
            }
            $bunga_per_bulan = ($deposito->bunga / 100 / 12) * $deposito->jumlah;
            $total_bunga_didapat = $months_passed * $bunga_per_bulan;

            $this->db->select_sum('jumlah');
            $this->db->where('deposito_id', $id);
            $q_pencairan = $this->db->get('tbl_deposito_bunga')->row();
            $total_dicairkan = $q_pencairan->jumlah ? $q_pencairan->jumlah : 0;

            $bunga_tersedia = $total_bunga_didapat - $total_dicairkan;

            echo json_encode(array(
                'ok' => true,
                'months_passed' => $months_passed,
                'bunga_per_bulan' => $bunga_per_bulan,
                'total_bunga_didapat' => $total_bunga_didapat,
                'total_dicairkan' => $total_dicairkan,
                'bunga_tersedia' => $bunga_tersedia
            ));
        } else {
            echo json_encode(array('ok' => false));
        }
    }
}
