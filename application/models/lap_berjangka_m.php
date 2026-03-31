<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

class Lap_berjangka_m extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
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

    function get_data_berjangka_ajax($offset, $limit, $q = '', $sort, $order)
    {
        $sql = "SELECT * FROM tbl_berjangka WHERE 1=1 ";
        if (is_array($q)) {
            if ($q['kode_transaksi'] != '') {
                $q['kode_transaksi'] = str_replace('TRB', '', $q['kode_transaksi']);
                $q['kode_transaksi'] = str_replace('AG', '', $q['kode_transaksi']);
                $q['kode_transaksi'] = $q['kode_transaksi'] * 1;
                $sql .= " AND (id LIKE '%" . $q['kode_transaksi'] . "%' OR anggota_id LIKE '%" . $q['kode_transaksi'] . "%') ";
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

    function get_jml_data_berjangka()
    {
        $sql = "SELECT * FROM tbl_berjangka WHERE 1=1 ";
        if (isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
            if ($_REQUEST['tgl_dari'] != '' && $_REQUEST['tgl_samp'] != '') {
                $sql .= " AND DATE(tgl_daftar) >= '" . $this->db->escape_str($_REQUEST['tgl_dari']) . "' AND DATE(tgl_daftar) <= '" . $this->db->escape_str($_REQUEST['tgl_samp']) . "' ";
            }
        }
        return $this->db->query($sql)->num_rows();
    }

    function get_data_berjangka($limit, $start)
    {
        $sql = "SELECT * FROM tbl_berjangka WHERE 1=1 ";
        if (isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
            if ($_REQUEST['tgl_dari'] != '' && $_REQUEST['tgl_samp'] != '') {
                $sql .= " AND DATE(tgl_daftar) >= '" . $this->db->escape_str($_REQUEST['tgl_dari']) . "' AND DATE(tgl_daftar) <= '" . $this->db->escape_str($_REQUEST['tgl_samp']) . "' ";
            }
        }
        $sql .= " ORDER BY tgl_daftar ASC LIMIT {$start}, {$limit}";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return FALSE;
        }
    }

    function lap_data_berjangka()
    {
        $sql = "SELECT * FROM tbl_berjangka WHERE 1=1 ";
        if (isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
            if ($_REQUEST['tgl_dari'] != '' && $_REQUEST['tgl_samp'] != '') {
                $sql .= " AND DATE(tgl_daftar) >= '" . $_REQUEST['tgl_dari'] . "' AND DATE(tgl_daftar) <= '" . $_REQUEST['tgl_samp'] . "' ";
            }
        }
        $sql .= " ORDER BY tgl_daftar ASC ";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return FALSE;
        }
    }
}
