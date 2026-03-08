<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

class Berjangka_m extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    function get_data_kas()
    {
        $this->db->select('*');
        $this->db->from('nama_kas_tbl');
        $this->db->where('aktif', 'Y');
        $this->db->where('tmpl_simpan', 'Y');
        $this->db->order_by('id', 'ASC');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return FALSE;
        }
    }

    function get_data_anggota($id)
    {
        $this->db->select('*');
        $this->db->from('tbl_anggota');
        $this->db->where('id', $id);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->row();
        } else {
            return FALSE;
        }
    }

    function get_data_transaksi_ajax($offset, $limit, $q = '', $sort, $order)
    {
        $sql = "SELECT * FROM tbl_berjangka WHERE 1=1 ";
        if (is_array($q)) {
            if ($q['kode_transaksi'] != '') {
                $q['kode_transaksi'] = str_replace('TRB', '', $q['kode_transaksi']);
                $q['kode_transaksi'] = str_replace('AG', '', $q['kode_transaksi']);
                $q['kode_transaksi'] = $q['kode_transaksi'] * 1;
                $sql .= " AND (id LIKE '%" . $q['kode_transaksi'] . "%' OR anggota_id LIKE '%" . $q['kode_transaksi'] . "%') ";
            }
            if ($q['cari_status'] != '') {
                $sql .= " AND status = '" . $q['cari_status'] . "' ";
            }
            if ($q['tgl_dari'] != '' && $q['tgl_sampai'] != '') {
                $sql .= " AND DATE(tgl_daftar) >= '" . $q['tgl_dari'] . "' ";
                $sql .= " AND DATE(tgl_daftar) <= '" . $q['tgl_sampai'] . "' ";
            }
        }
        $result['count'] = $this->db->query($sql)->num_rows();
        $sql .= " ORDER BY {$sort} {$order} ";
        $sql .= " LIMIT {$offset}, {$limit} ";
        $result['data'] = $this->db->query($sql)->result();
        return $result;
    }

    function get_data_setor_ajax($offset, $limit, $berjangka_id, $sort, $order)
    {
        $sql = "SELECT * FROM tbl_trans_berjangka WHERE berjangka_id = '" . $this->db->escape_str($berjangka_id) . "' ";
        $result['count'] = $this->db->query($sql)->num_rows();
        $sql .= " ORDER BY {$sort} {$order} ";
        $sql .= " LIMIT {$offset}, {$limit} ";
        $result['data'] = $this->db->query($sql)->result();
        return $result;
    }

    public function create()
    {
        if (str_replace(',', '', $this->input->post('setoran_per_bulan')) <= 0) {
            return FALSE;
        }
        $tgl_daftar = $this->input->post('tgl_daftar');
        $lama_bulan = $this->input->post('lama_bulan');

        // Hitung tanggal jatuh tempo
        $tgl_jatuh_tempo = date('Y-m-d H:i:s', strtotime("+$lama_bulan months", strtotime($tgl_daftar)));

        $data = array(
            'tgl_daftar'        =>    $tgl_daftar,
            'anggota_id'        =>    $this->input->post('anggota_id'),
            'setoran_per_bulan'    =>    str_replace(',', '', $this->input->post('setoran_per_bulan')),
            'lama_bulan'        =>    $lama_bulan,
            'bunga'                =>    str_replace(',', '', $this->input->post('bunga')),
            'tgl_jatuh_tempo'    =>    $tgl_jatuh_tempo,
            'total_terkumpul'   =>  0,
            'status'            =>    'Aktif',
            'keterangan'        =>  $this->input->post('ket'),
            'kas_id'            =>    $this->input->post('kas_id'),
            'user_name'            =>  $this->data['u_name'],
            'update_data'        =>  date('Y-m-d H:i:s')
        );
        $insert = $this->db->insert('tbl_berjangka', $data);

        // Apabila ada setoran awal langsung (opsional), namun disini dibuat form terpisah di "Setor"
        return $insert;
    }

    public function setor()
    {
        $berjangka_id = $this->input->post('berjangka_id');
        $jumlah_setor = str_replace(',', '', $this->input->post('jumlah_setor'));

        if ($jumlah_setor <= 0) return FALSE;

        $tgl_transaksi = $this->input->post('tgl_transaksi');

        // Insert history transaksi
        $data_trans = array(
            'berjangka_id'  => $berjangka_id,
            'tgl_transaksi' => $tgl_transaksi,
            'jumlah'        => $jumlah_setor,
            'keterangan'    => $this->input->post('ket_setor'),
            'kas_id'        => $this->input->post('kas_id_setor'),
            'user_name'     => $this->data['u_name']
        );
        $insert = $this->db->insert('tbl_trans_berjangka', $data_trans);

        if ($insert) {
            // Update total_terkumpul di tabel utama
            // Get current
            $this->db->where('id', $berjangka_id);
            $ber = $this->db->get('tbl_berjangka')->row();
            $new_total = $ber->total_terkumpul + $jumlah_setor;

            $this->db->where('id', $berjangka_id);
            $this->db->update('tbl_berjangka', array(
                'total_terkumpul' => $new_total,
                'update_data'     => date('Y-m-d H:i:s')
            ));
            return TRUE;
        }
        return FALSE;
    }

    public function pencairan($id)
    {
        $tanggal_u = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        return $this->db->update('tbl_berjangka', array(
            'status'        =>    'Cair',
            'update_data'    =>     $tanggal_u,
            'user_name'        =>     $this->data['u_name']
        ));
    }

    public function delete($id)
    {
        $this->db->delete('tbl_trans_berjangka', array('berjangka_id' => $id));
        return $this->db->delete('tbl_berjangka', array('id' => $id));
    }
}
