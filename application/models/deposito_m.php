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
        return $this->db->update('tbl_deposito', array(
            'status'                =>    'Cair',
            'update_data'            =>     $tanggal_u,
            'user_name'                =>     $this->data['u_name']
        ));
    }

    public function delete($id)
    {
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
        if (str_replace(',', '', $this->input->post('jumlah_bunga')) <= 0) {
            return FALSE;
        }
        $data = array(
            'deposito_id'     => $this->input->post('deposito_id_bunga'),
            'tgl_pencairan'   => $this->input->post('tgl_pencairan_bunga'),
            'jumlah'          => str_replace(',', '', $this->input->post('jumlah_bunga')),
            'kas_id'          => $this->input->post('kas_id_bunga'),
            'metode'          => $this->input->post('metode_pencairan'),
            'keterangan'      => $this->input->post('ket_bunga'),
            'user_name'       => $this->data['u_name']
        );
        return $this->db->insert('tbl_deposito_bunga', $data);
    }
}
