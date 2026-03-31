<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

class Lap_deposito_m extends CI_Model
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

    function get_data_deposito_ajax($offset, $limit, $q = '', $sort, $order)
    {
        $sql = "SELECT * FROM tbl_deposito WHERE 1=1 ";
        if (is_array($q)) {
            if ($q['kode_transaksi'] != '') {
                $q['kode_transaksi'] = str_replace('TRD', '', $q['kode_transaksi']);
                $q['kode_transaksi'] = str_replace('AG', '', $q['kode_transaksi']);
                $q['kode_transaksi'] = $q['kode_transaksi'] * 1;
                $sql .= " AND (id LIKE '%" . $q['kode_transaksi'] . "%' OR anggota_id LIKE '%" . $q['kode_transaksi'] . "%') ";
            }
            if ($q['tgl_dari'] != '' && $q['tgl_sampai'] != '') {
                $sql .= " AND DATE(tgl_deposito) >= '" . $q['tgl_dari'] . "' ";
                $sql .= " AND DATE(tgl_deposito) <= '" . $q['tgl_sampai'] . "' ";
            }
        }
        $result['count'] = $this->db->query($sql)->num_rows();
        $sql .= " ORDER BY {$sort} {$order} ";
        $sql .= " LIMIT {$offset}, {$limit} ";
        $result['data'] = $this->db->query($sql)->result();
        return $result;
    }

    function get_jml_data_deposito()
    {
        $sql = "SELECT * FROM tbl_deposito WHERE 1=1 ";
        if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
            if($_REQUEST['tgl_dari'] != '' && $_REQUEST['tgl_samp'] != '') {
                $sql .= " AND DATE(tgl_deposito) >= '".$this->db->escape_str($_REQUEST['tgl_dari'])."' AND DATE(tgl_deposito) <= '".$this->db->escape_str($_REQUEST['tgl_samp'])."' ";
            }
        }
        return $this->db->query($sql)->num_rows();
    }

    function get_data_deposito($limit, $start)
    {
        $sql = "SELECT * FROM tbl_deposito WHERE 1=1 ";
        if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
            if($_REQUEST['tgl_dari'] != '' && $_REQUEST['tgl_samp'] != '') {
                $sql .= " AND DATE(tgl_deposito) >= '".$this->db->escape_str($_REQUEST['tgl_dari'])."' AND DATE(tgl_deposito) <= '".$this->db->escape_str($_REQUEST['tgl_samp'])."' ";
            }
        }
        $sql .= " ORDER BY tgl_deposito ASC LIMIT {$start}, {$limit}";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return FALSE;
        }
    }

    function lap_data_deposito()
    {
        $sql = "SELECT * FROM tbl_deposito WHERE 1=1 ";
        if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
            if($_REQUEST['tgl_dari'] != '' && $_REQUEST['tgl_samp'] != '') {
                $sql .= " AND DATE(tgl_deposito) >= '".$_REQUEST['tgl_dari']."' AND DATE(tgl_deposito) <= '".$_REQUEST['tgl_samp']."' ";
            }
        }
        $sql .= " ORDER BY tgl_deposito ASC ";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return FALSE;
        }
    }
}
