<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

class Deposito_m extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    #panggil data kas
    function get_data_kas()
    {
        $this->db->select('*');
        $this->db->from('nama_kas_tbl');
        $this->db->where('aktif', 'Y');
        $this->db->where('tmpl_simpan', 'Y');
        $this->db->order_by('id', 'ASC');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $out = $query->result();
            return $out;
        } else {
            return FALSE;
        }
    }

    //panggil data anggota
    function get_data_anggota($id)
    {
        $this->db->select('*');
        $this->db->from('tbl_anggota');
        $this->db->where('id', $id);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $out = $query->row();
            return $out;
        } else {
            return FALSE;
        }
    }

    //hitung jumlah total deposito
    function get_jml_deposito()
    {
        $this->db->select('SUM(jumlah) AS jml_total');
        $this->db->from('tbl_deposito');
        $this->db->where('status', 'Aktif');
        $query = $this->db->get();
        return $query->row();
    }

    //panggil data deposito untuk esyui
    function get_data_transaksi_ajax($offset, $limit, $q = '', $sort, $order)
    {
        $sql = "SELECT * FROM tbl_deposito WHERE 1=1 ";
        if (is_array($q)) {
            if ($q['kode_transaksi'] != '') {
                $q['kode_transaksi'] = str_replace('TRD', '', $q['kode_transaksi']);
                $q['kode_transaksi'] = str_replace('AG', '', $q['kode_transaksi']);
                $q['kode_transaksi'] = $q['kode_transaksi'] * 1;
                $sql .= " AND (id LIKE '" . $q['kode_transaksi'] . "' OR anggota_id LIKE '" . $q['kode_transaksi'] . "') ";
            } else {
                if ($q['cari_status'] != '') {
                    $sql .= " AND status = '" . $q['cari_status'] . "' ";
                }

                if ($q['tgl_dari'] != '' && $q['tgl_sampai'] != '') {
                    $sql .= " AND DATE(tgl_deposito) >= '" . $q['tgl_dari'] . "' ";
                    $sql .= " AND DATE(tgl_deposito) <= '" . $q['tgl_sampai'] . "' ";
                }
            }
        }
        $result['count'] = $this->db->query($sql)->num_rows();
        $sql .= " ORDER BY {$sort} {$order} ";
        $sql .= " LIMIT {$offset},{$limit} ";
        $result['data'] = $this->db->query($sql)->result();
        return $result;
    }

    public function create()
    {
        if (str_replace(',', '', $this->input->post('jumlah')) <= 0) {
            return FALSE;
        }
        $tgl_deposito = $this->input->post('tgl_deposito');
        $lama_bulan = $this->input->post('lama_bulan');

        // hitung tgl jatuh tempo
        $tgl_jatuh_tempo = date('Y-m-d H:i:s', strtotime("+$lama_bulan months", strtotime($tgl_deposito)));

        $data = array(
            'tgl_deposito'            =>    $this->input->post('tgl_deposito'),
            'anggota_id'            =>    $this->input->post('anggota_id'),
            'jumlah'                =>    str_replace(',', '', $this->input->post('jumlah')),
            'lama_bulan'            =>    $this->input->post('lama_bulan'),
            'bunga'                    =>    str_replace(',', '', $this->input->post('bunga')),
            'tgl_jatuh_tempo'        =>    $tgl_jatuh_tempo,
            'status'                =>    'Aktif',
            'keterangan'            =>  $this->input->post('ket'),
            'kas_id'                =>    $this->input->post('kas_id'),
            'user_name'                =>  $this->data['u_name'],
            'update_data'            =>  date('Y-m-d H:i:s')
        );
        return $this->db->insert('tbl_deposito', $data);
    }

    public function pencairan($id)
    {
        $tanggal_u = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        $update = $this->db->update('tbl_deposito', array(
            'status'                =>    'Cair',
            'update_data'            =>     $tanggal_u,
            'user_name'                =>     $this->data['u_name']
        ));

        if ($update) {
            $deposito = $this->db->get_where('tbl_deposito', array('id' => $id))->row();
            if ($deposito) {
                // Calculate remaining interest
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
                $total_kembali = $deposito->jumlah + $bunga_tersedia;

                $data_sp = array(
                    'tgl_transaksi' => $tanggal_u,
                    'anggota_id'    => $deposito->anggota_id,
                    'jenis_id'      => 42, // 42 = Simpanan Deposito
                    'jumlah'        => $total_kembali,
                    'keterangan'    => 'Pencairan Pokok & Sisa Bunga Deposito TRD' . sprintf('%05d', $id),
                    'akun'          => 'Setoran',
                    'dk'            => 'D',
                    'kas_id'        => $deposito->kas_id,
                    'user_name'     => $this->data['u_name']
                );
                $this->db->insert('tbl_trans_sp', $data_sp);
            }
        }
        return $update;
    }

    public function delete($id)
    {
        // Hapus pencatatan simpanan deposito (bunga maupun pokok yang sudah masuk ke Tabungan)
        $this->db->where('jenis_id', 42);
        $this->db->like('keterangan', 'Deposito TRD' . sprintf('%05d', $id));
        $this->db->delete('tbl_trans_sp');

        $this->db->delete('tbl_deposito_bunga', array('deposito_id' => $id));
        return $this->db->delete('tbl_deposito', array('id' => $id));
    }

    function get_data_bunga_ajax($offset, $limit, $deposito_id, $sort, $order)
    {
        $sql = "SELECT * FROM tbl_deposito_bunga WHERE 1=1 ";
        if ($deposito_id != '') {
            $sql .= " AND deposito_id = " . intval($deposito_id) . " ";
        }
        $result['count'] = $this->db->query($sql)->num_rows();
        $sql .= " ORDER BY {$sort} {$order} ";
        $sql .= " LIMIT {$offset},{$limit} ";
        $result['data'] = $this->db->query($sql)->result();
        return $result;
    }

    public function create_bunga()
    {
        $jumlah_bunga = str_replace(',', '', $this->input->post('jumlah_bunga'));
        if ($jumlah_bunga <= 0) {
            return FALSE;
        }

        $deposito_id = $this->input->post('deposito_id_bunga');
        $metode = $this->input->post('metode_pencairan');
        $tgl_pencairan = $this->input->post('tgl_pencairan_bunga');
        $kas_id = $this->input->post('kas_id_bunga');
        $keterangan = $this->input->post('ket_bunga');

        $data = array(
            'deposito_id'     => $deposito_id,
            'tgl_pencairan'   => $tgl_pencairan,
            'jumlah'          => $jumlah_bunga,
            'kas_id'          => $kas_id,
            'metode'          => $metode,
            'keterangan'      => $keterangan,
            'user_name'       => $this->data['u_name']
        );
        
        $insert_bunga = $this->db->insert('tbl_deposito_bunga', $data);

        if ($insert_bunga && $metode == 'Tabungan') {
            $deposito = $this->db->get_where('tbl_deposito', array('id' => $deposito_id))->row();
            if ($deposito) {
                $data_sp = array(
                    'tgl_transaksi' => $tgl_pencairan,
                    'anggota_id'    => $deposito->anggota_id,
                    'jenis_id'      => 42, // 42 = Simpanan Deposito
                    'jumlah'        => $jumlah_bunga,
                    'keterangan'    => 'Pencairan Bunga Deposito TRD' . sprintf('%05d', $deposito_id) . ' - ' . $keterangan,
                    'akun'          => 'Setoran',
                    'dk'            => 'D',
                    'kas_id'        => $kas_id,
                    'user_name'     => $this->data['u_name']
                );
                $this->db->insert('tbl_trans_sp', $data_sp);
            }
        }

        return $insert_bunga;
    }
}
