<!-- Styler -->
<style type="text/css">
    td,
    div {
        font-family: "Arial", "​Helvetica", "​sans-serif";
    }

    .datagrid-header-row * {
        font-weight: bold;
    }

    .messager-window * a:focus,
    .messager-window * span:focus {
        color: blue;
        font-weight: bold;
    }

    .daterangepicker * {
        font-family: "Source Sans Pro", "Arial", "​Helvetica", "​sans-serif";
        box-sizing: border-box;
    }

    .glyphicon {
        font-family: "Glyphicons Halflings"
    }
</style>

<!-- Data Grid -->
<?php
# buat tanggal sekarang
$tanggal = date('Y-m-d H:i');
$tanggal_arr = explode(' ', $tanggal);
$txt_tanggal = jin_date_ina($tanggal_arr[0]);
$txt_tanggal .= ' - ' . $tanggal_arr[1];
?>
<table id="dg"
    class="easyui-datagrid"
    title="Data Tabungan Deposito"
    style="width:auto; height: auto;"
    url="<?php echo site_url('deposito/ajax_list'); ?>"
    pagination="true" rownumbers="true"
    fitColumns="true" singleSelect="true" collapsible="true"
    sortName="tgl_deposito" sortOrder="DESC"
    toolbar="#tb"
    striped="true">
    <thead>
        <tr>
            <th data-options="field:'id',halign:'center', align:'center'" hidden="true">ID</th>
            <th data-options="field:'id_txt', width:'15', halign:'center', align:'center'">Kode </th>
            <th data-options="field:'tgl_deposito', halign:'center', align:'center'" hidden="true">Tanggal</th>
            <th data-options="field:'tgl_deposito_txt', width:'25', halign:'center', align:'center'">Tanggal <br> Deposito</th>
            <th data-options="field:'anggota_id',halign:'center', align:'center'" hidden="true">ID</th>
            <th data-options="field:'anggota_id_txt', width:'15', halign:'center', align:'center'">ID Anggota</th>
            <th data-options="field:'nama', width:'30', halign:'center', align:'left'">Nama Anggota</th>
            <th data-options="field:'departement', width:'20', halign:'center', align:'left'">Dept</th>
            <th data-options="field:'jumlah', width:'20', halign:'center', align:'right'">Total Deposito</th>
            <th data-options="field:'lama_bulan', width:'15', halign:'center', align:'center'">Lama Waktu</th>
            <th data-options="field:'bunga', width:'10', halign:'center', align:'center'">Bunga/Thn</th>
            <th data-options="field:'tgl_jatuh_tempo_txt', width:'20', halign:'center', align:'center'">Tanggal <br> Jatuh Tempo</th>
            <th data-options="field:'estimasi_bunga', width:'20', halign:'center', align:'right'">Estimasi Bunga</th>
            <th data-options="field:'total_kembali', width:'20', halign:'center', align:'right'">Total Pencairan</th>
            <th data-options="field:'status', width:'15', halign:'center', align:'center'">Status</th>
            <th data-options="field:'ket', width:'20', halign:'center', align:'left'">Keterangan</th>
            <th data-options="field:'user', width:'15', halign:'center', align:'center'">User</th>
        </tr>
    </thead>
</table>

<!-- Toolbar -->
<div id="tb" style="height: 35px;">
    <div style="vertical-align: middle; display: inline; padding-top: 15px;">
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="create()">Tambah </a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" plain="true" onclick="pencairan()">Pencairan Deposito</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" plain="true" onclick="pencairan_bunga()">Pencairan Bunga</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-search" plain="true" onclick="history_bunga()">History Bunga</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" plain="true" onclick="hapus()">Hapus</a>
    </div>
    <div class="pull-right" style="vertical-align: middle;">
        <div id="filter_tgl" class="input-group" style="display: inline;">
            <button class="btn btn-default" id="daterange-btn">
                <i class="fa fa-calendar"></i> <span id="reportrange"><span>Pilih Tanggal</span></span>
                <i class="fa fa-caret-down"></i>
            </button>
        </div>
        <select id="cari_status" name="cari_status" style="width:170px; height:27px">
            <option value=""> -- Semua Status --</option>
            <option value="Aktif">Aktif</option>
            <option value="Cair">Telah Cair</option>
        </select>
        <span>Cari :</span>
        <input name="kode_transaksi" id="kode_transaksi" size="23" placeholder="Kode Transaksi" style="line-height:22px;border:1px solid #ccc">

        <a href="javascript:void(0);" iconCls="icon-search" class="easyui-linkbutton" onclick="doSearch()">Cari</a>
        <a href="javascript:void(0);" iconCls="icon-clear" class="easyui-linkbutton" onclick="clearSearch()">Bersihkan</a>
    </div>
</div>

<!-- Dialog Form -->
<div id="dialog-form" class="easyui-dialog" show="blind" hide="blind" modal="true" resizable="false" style="width:500px; height:450px; padding-left: 15px; padding-top:20px" closed="true" buttons="#dialog-buttons" style="display: none;">
    <form id="form" method="post" novalidate>
        <table>
            <tr>
                <td>
                    <table>
                        <tr style="height:35px">
                            <td>Tanggal Deposito</td>
                            <td>:</td>
                            <td>
                                <div class="input-group date dtpicker col-md-5" style="z-index: 9999 !important;">
                                    <input type="text" name="tgl_deposito_txt" id="tgl_deposito_txt" style=" background:#eee; width:155px; height:23px" required="true" readonly="readonly" />
                                    <input type="hidden" name="tgl_deposito" id="tgl_deposito" />
                                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                </div>
                            </td>
                        </tr>
                        <tr style="height:35px">
                            <td>Nama Anggota</td>
                            <td>:</td>
                            <td>
                                <input id="anggota_id" name="anggota_id" style="width:195px; height:25px" class="easyui-validatebox" required="true">
                            </td>
                        </tr>
                        <tr style="height:35px">
                            <td>Jumlah Deposito</td>
                            <td>:</td>
                            <td>
                                <input class="" id="jumlah" name="jumlah" style="width:195px; height:25px; " required="true" />
                            </td>
                        </tr>
                        <tr style="height:35px">
                            <td>Lama Simpan (Bulan)</td>
                            <td>:</td>
                            <td>
                                <select id="lama_bulan" name="lama_bulan" style="width:200px; height:23px" class="easyui-validatebox" required="true">
                                    <option value="0"> -- Pilih Jangka Waktu --</option>
                                    <option value="1">1 Bulan</option>
                                    <option value="3">3 Bulan</option>
                                    <option value="6">6 Bulan</option>
                                    <option value="12">12 Bulan</option>
                                    <option value="24">24 Bulan</option>
                                </select>
                            </td>
                        </tr>
                        <tr style="height:35px">
                            <td>Bunga Per Tahun (%)</td>
                            <td>:</td>
                            <td>
                                <input type="text" id="bunga" name="bunga" style="width:195px; height:23px" required="true" />
                            </td>
                        </tr>
                        <tr style="height:35px">
                            <td>Simpan Ke Kas</td>
                            <td>:</td>
                            <td>
                                <select id="kas" name="kas_id" style="width:200px; height:23px" class="easyui-validatebox" required="true">
                                    <option value="0"> -- Pilih Kas --</option>
                                    <?php
                                    foreach ($kas_id as $row) {
                                        echo '<option value="' . $row->id . '">' . $row->nama . '</option>';
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr style="height:35px">
                            <td>Keterangan</td>
                            <td>:</td>
                            <td>
                                <input id="ket" name="ket" style="width:190px; height:20px">
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="10px"></td>
                <td valign="top"> Photo : <br>
                    <div id="anggota_poto" style="height:120px; width:90px; border:1px solid #ccc"> </div>
                </td>
            </tr>
        </table>
    </form>
</div>

<!-- Dialog Button -->
<div id="dialog-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="save()">Simpan</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:jQuery('#dialog-form').dialog('close')">Batal</a>
</div>

<!-- Dialog Form Bunga -->
<div id="dialog-form-bunga" class="easyui-dialog" show="blind" hide="blind" modal="true" resizable="false" style="width:480px; height:480px; padding-left: 15px; padding-top:20px; padding-right:15px;" closed="true" buttons="#dialog-buttons-bunga" style="display: none;">
    <div style="background:#f4f4f4; padding:10px; margin-bottom:15px; border:1px solid #ddd; font-size: 11px;">
        <table style="width:100%">
            <tr>
                <td width="30%"><strong>Bulan Berjalan:</strong></td>
                <td width="20%"><span id="f_sum_bulan_berjalan">0</span> Bln</td>
                <td width="25%"><strong>Total Didapat:</strong></td>
                <td width="25%">Rp <span id="f_sum_total_didapat">0</span></td>
            </tr>
            <tr>
                <td><strong>Bunga/Bulan:</strong></td>
                <td>Rp <span id="f_sum_bunga_per_bulan">0</span></td>
                <td><strong>Telah Cair:</strong></td>
                <td>Rp <span id="f_sum_total_dicairkan">0</span></td>
            </tr>
            <tr>
                <td colspan="2"></td>
                <td><strong>Sisa Tersedia:</strong></td>
                <td style="color:red; font-weight:bold;">Rp <span id="f_sum_bunga_tersedia">0</span></td>
            </tr>
        </table>
    </div>

    <form id="form_bunga" method="post" novalidate>
        <input type="hidden" name="deposito_id_bunga" id="deposito_id_bunga" />
        <table>
            <tr style="height:35px">
                <td>Tanggal Pencairan</td>
                <td>:</td>
                <td>
                    <div class="input-group date dtpicker col-md-5" style="z-index: 9999 !important;">
                        <input type="text" name="tgl_pencairan_bunga_txt" id="tgl_pencairan_bunga_txt" style=" background:#eee; width:155px; height:23px" required="true" readonly="readonly" />
                        <input type="hidden" name="tgl_pencairan_bunga" id="tgl_pencairan_bunga" />
                        <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                    </div>
                </td>
            </tr>
            <tr style="height:35px">
                <td>Anggota</td>
                <td>:</td>
                <td>
                    <input id="anggota_bunga_txt" name="anggota_bunga_txt" style="width:195px; height:23px" readonly="readonly" style="background:#eee; border:1px solid #ccc; padding-left:5px;">
                </td>
            </tr>
            <tr style="height:35px">
                <td>Jumlah Bunga</td>
                <td>:</td>
                <td>
                    <input class="" id="jumlah_bunga" name="jumlah_bunga" style="width:195px; height:25px; " required="true" />
                </td>
            </tr>
            <tr style="height:35px">
                <td>Metode Pencairan</td>
                <td>:</td>
                <td>
                    <select id="metode_pencairan" name="metode_pencairan" style="width:200px; height:23px" class="easyui-validatebox" required="true">
                        <option value="Cash">Cash (Tunai)</option>
                        <option value="Transfer">Transfer Rekening</option>
                    </select>
                </td>
            </tr>
            <tr style="height:35px">
                <td>Ambil Dari Kas</td>
                <td>:</td>
                <td>
                    <select id="kas_id_bunga" name="kas_id_bunga" style="width:200px; height:23px" class="easyui-validatebox" required="true">
                        <option value="0"> -- Pilih Kas --</option>
                        <?php
                        foreach ($kas_id as $row) {
                            echo '<option value="' . $row->id . '">' . $row->nama . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr style="height:35px">
                <td>Keterangan</td>
                <td>:</td>
                <td>
                    <input id="ket_bunga" name="ket_bunga" style="width:190px; height:20px">
                </td>
            </tr>
        </table>
    </form>
</div>
<div id="dialog-buttons-bunga">
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="save_bunga()">Simpan</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:jQuery('#dialog-form-bunga').dialog('close')">Batal</a>
</div>

<!-- Dialog History Bunga -->
<div id="dialog-history-bunga" class="easyui-dialog" show="blind" hide="blind" modal="true" resizable="false" style="width:700px; height:500px; padding: 15px;" closed="true" buttons="#dialog-buttons-history" style="display: none;">
    <div style="background:#f4f4f4; padding:10px; margin-bottom:10px; border:1px solid #ddd;">
        <table style="width:100%">
            <tr>
                <td width="30%"><strong>Bunga Bulan Berjalan:</strong></td>
                <td width="20%"><span id="sum_bulan_berjalan">0</span> Bulan</td>
                <td width="25%"><strong>Total Didapat:</strong></td>
                <td width="25%">Rp <span id="sum_total_didapat">0</span></td>
            </tr>
            <tr>
                <td><strong>Estimasi Bunga/Bulan:</strong></td>
                <td>Rp <span id="sum_bunga_per_bulan">0</span></td>
                <td><strong>Sudah Dicairkan:</strong></td>
                <td>Rp <span id="sum_total_dicairkan">0</span></td>
            </tr>
            <tr>
                <td colspan="2"></td>
                <td><strong>Sisa Tersedia:</strong></td>
                <td style="color:red; font-weight:bold;">Rp <span id="sum_bunga_tersedia">0</span></td>
            </tr>
        </table>
    </div>

    <table id="dg_bunga"
        class="easyui-datagrid"
        style="width:100%; height: 300px;"
        url="<?php echo site_url('deposito/ajax_list_bunga'); ?>"
        pagination="true" rownumbers="true"
        fitColumns="true" singleSelect="true"
        sortName="tgl_pencairan" sortOrder="DESC"
        striped="true">
        <thead>
            <tr>
                <th data-options="field:'id',halign:'center', align:'center'" hidden="true">ID</th>
                <th data-options="field:'tgl_pencairan_txt', width:'25', halign:'center', align:'center'">Tanggal Pencairan</th>
                <th data-options="field:'jumlah', width:'25', halign:'center', align:'right'">Jumlah Bunga</th>
                <th data-options="field:'metode', width:'15', halign:'center', align:'center'">Metode</th>
                <th data-options="field:'keterangan', width:'30', halign:'center', align:'left'">Keterangan</th>
                <th data-options="field:'user_name', width:'15', halign:'center', align:'center'">User</th>
            </tr>
        </thead>
    </table>
</div>
<div id="dialog-buttons-history">
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:jQuery('#dialog-history-bunga').dialog('close')">Tutup</a>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $(".dtpicker").datetimepicker({
            language: 'id',
            weekStart: 1,
            autoclose: true,
            todayBtn: true,
            todayHighlight: true,
            pickerPosition: 'bottom-right',
            format: "dd MM yyyy - hh:ii",
            linkField: "tgl_deposito",
            linkFormat: "yyyy-mm-dd hh:ii"
        });

        $('#anggota_id').combogrid({
            panelWidth: 400,
            url: '<?php echo site_url('deposito/list_anggota'); ?>',
            idField: 'id',
            valueField: 'id',
            textField: 'nama',
            mode: 'remote',
            fitColumns: true,
            columns: [
                [{
                        field: 'photo',
                        title: 'Photo',
                        align: 'center',
                        width: 5
                    },
                    {
                        field: 'id',
                        title: 'ID',
                        hidden: true
                    },
                    {
                        field: 'kode_anggota',
                        title: 'ID',
                        align: 'center',
                        width: 15
                    },
                    {
                        field: 'nama',
                        title: 'Nama Anggota',
                        align: 'left',
                        width: 15
                    },
                    {
                        field: 'kota',
                        title: 'Kota',
                        align: 'left',
                        width: 10
                    }
                ]
            ],
            onSelect: function(record) {
                $("#anggota_poto").html('<img src="<?php echo base_url(); ?>assets/theme_admin/img/loading.gif" />');
                var val_anggota_id = $('input[name=anggota_id]').val();
                $.ajax({
                        url: '<?php echo site_url(); ?>deposito/get_anggota_by_id/' + val_anggota_id,
                        type: 'POST',
                        dataType: 'html',
                        data: {
                            anggota_id: val_anggota_id
                        },
                    })
                    .done(function(result) {
                        $('#anggota_poto').html(result);
                    })
                    .fail(function() {
                        alert('Koneksi error, silahkan ulangi.')
                    });
            }
        });

        $("#cari_status").change(function() {
            $('#dg').datagrid('load', {
                cari_status: $('#cari_status').val()
            });
        });

        $("#kode_transaksi").keyup(function(event) {
            if (event.keyCode == 13) {
                $("#btn_filter").click();
            }
        });

        $("#kode_transaksi").keyup(function(e) {
            var isi = $(e.target).val();
            $(e.target).val(isi.toUpperCase());
        });

        $('#jumlah').keyup(function() {
            var val_jumlah = $(this).val();
            $('#jumlah').val(number_format(val_jumlah));
        });

        $('#jumlah_bunga').keyup(function() {
            var val_jumlah = $(this).val();
            $('#jumlah_bunga').val(number_format(val_jumlah));
        });

        fm_filter_tgl();
    }); //ready


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
                showDropdowns: true,
                format: 'YYYY-MM-DD',
                startDate: moment().startOf('year').startOf('month'),
                endDate: moment().endOf('year').endOf('month')
            },
            function(start, end) {
                $('#reportrange span').html(start.format('D MMM YYYY') + ' - ' + end.format('D MMM YYYY'));
                doSearch();
            });
    }
</script>

<script type="text/javascript">
    var url;

    function create() {
        jQuery('#dialog-form').dialog('open').dialog('setTitle', 'Form Tambah Deposito Tabungan');
        jQuery('#form').form('clear');
        $('#anggota_id ~ span span a').show();
        $('#anggota_id ~ span input').removeAttr('disabled');
        $('#anggota_id ~ span input').focus();

        jQuery('#tgl_deposito_txt').val('<?php echo $txt_tanggal; ?>');
        jQuery('#tgl_deposito').val('<?php echo $tanggal; ?>');
        jQuery('#kas option[value="0"]').prop('selected', true);
        jQuery('#lama_bulan option[value="0"]').prop('selected', true);
        $("#anggota_poto").html('');

        url = '<?php echo site_url('deposito/create'); ?>';
    }

    function save() {
        var string = $("#form").serialize();
        //validasi teks kosong
        var anggota_id = $('#anggota_id').combogrid('getValue');
        if (anggota_id == '' || anggota_id == null || anggota_id == undefined) {
            $.messager.show({
                title: '<div><i class="fa fa-warning"></i> Peringatan ! </div>',
                msg: '<div class="text-red"><i class="fa fa-ban"></i> Maaf, Anggota belum dipilih. </div>',
                timeout: 2000,
                showType: 'slide'
            });
            $("#anggota_id").focus();
            return false;
        }
        var jumlah = $("#jumlah").val();
        if (jumlah <= 0 || jumlah == '') {
            $.messager.show({
                title: '<div><i class="fa fa-warning"></i> Peringatan ! </div>',
                msg: '<div class="text-red"><i class="fa fa-ban"></i> Maaf, Jumlah harus diisi.</div>',
                timeout: 2000,
                showType: 'slide'
            });
            $("#jumlah").focus();
            return false;
        }

        var lama_bulan = $("#lama_bulan").val();
        if (lama_bulan == 0) {
            $.messager.show({
                title: '<div><i class="fa fa-warning"></i> Peringatan ! </div>',
                msg: '<div class="text-red"><i class="fa fa-ban"></i> Maaf, Lama Waktu belum dipilih </div>',
                timeout: 2000,
                showType: 'slide'
            });
            $("#lama_bulan").focus();
            return false;
        }

        var bunga = $("#bunga").val();
        if (bunga == '') {
            $.messager.show({
                title: '<div><i class="fa fa-warning"></i> Peringatan ! </div>',
                msg: '<div class="text-red"><i class="fa fa-ban"></i> Maaf, Bunga harus diisi </div>',
                timeout: 2000,
                showType: 'slide'
            });
            $("#bunga").focus();
            return false;
        }

        var kas = $("#kas").val();
        if (kas == 0) {
            $.messager.show({
                title: '<div><i class="fa fa-warning"></i> Peringatan ! </div>',
                msg: '<div class="text-red"><i class="fa fa-ban"></i> Maaf, Ambil dari Kas harus diisi.</div>',
                timeout: 2000,
                showType: 'slide'
            });
            $("#kas").focus();
            return false;
        }

        $.ajax({
            type: "POST",
            url: url,
            data: string,
            success: function(result) {
                var result = eval('(' + result + ')');
                $.messager.show({
                    title: '<div><i class="fa fa-info"></i> Informasi</div>',
                    msg: result.msg,
                    timeout: 2000,
                    showType: 'slide'
                });
                if (result.ok) {
                    jQuery('#dialog-form').dialog('close');
                    $('#dg').datagrid('reload');
                }
            }
        });
    }

    function pencairan() {
        var row = jQuery('#dg').datagrid('getSelected');
        if (row) {
            if (row.status === '<span class="label label-default">Cair</span>') {
                $.messager.show({
                    title: '<div><i class="fa fa-warning"></i> Peringatan !</div>',
                    msg: '<div class="text-red"><i class="fa fa-ban"></i> Maaf, Data deposito ini sudah dicairkan.</div>',
                    timeout: 2000,
                    showType: 'slide'
                });
                return false;
            }

            $.messager.confirm('Konfirmasi', 'Apakah anda yakin merubah status deposito <code>' + row.id_txt + '</code> (' + row.nama + ') menjadi CAIR? Saldo deposito dan bunga akan diasumsikan sudah dikembalikan.', function(r) {
                if (r) {
                    $.ajax({
                        type: "POST",
                        url: "<?php echo site_url('deposito/pencairan'); ?>",
                        data: 'id=' + row.id,
                        success: function(result) {
                            var result = eval('(' + result + ')');
                            $.messager.show({
                                title: '<div><i class="fa fa-info"></i> Informasi</div>',
                                msg: result.msg,
                                timeout: 2000,
                                showType: 'slide'
                            });
                            if (result.ok) {
                                $('#dg').datagrid('reload');
                            }
                        },
                        error: function() {
                            $.messager.show({
                                title: '<div><i class="fa fa-warning"></i> Peringatan !</div>',
                                msg: '<div class="text-red"><i class="fa fa-ban"></i> Maaf, Terjadi kesalahan koneksi, silahkan muat ulang !!</div>',
                                timeout: 2000,
                                showType: 'slide'
                            });
                        }
                    });
                }
            });
        } else {
            $.messager.show({
                title: '<div><i class="fa fa-warning"></i> Peringatan !</div>',
                msg: '<div class="text-red"><i class="fa fa-ban"></i> Maaf, Data harus dipilih terlebih dahulu </div>',
                timeout: 2000,
                showType: 'slide'
            });
        }
    }

    function hapus() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $.messager.confirm('Konfirmasi', 'Apakah anda yakin akan menghapus data deposito <code>' + row.id_txt + '</code> ?', function(r) {
                if (r) {
                    $.ajax({
                        type: "POST",
                        url: "<?php echo site_url('deposito/delete'); ?>",
                        data: 'id=' + row.id,
                        success: function(result) {
                            var result = eval('(' + result + ')');
                            $.messager.show({
                                title: '<div><i class="fa fa-info"></i> Informasi</div>',
                                msg: result.msg,
                                timeout: 2000,
                                showType: 'slide'
                            });
                            if (result.ok) {
                                $('#dg').datagrid('reload');
                            }
                        },
                        error: function() {
                            $.messager.show({
                                title: '<div><i class="fa fa-warning"></i> Peringatan !</div>',
                                msg: '<div class="text-red"><i class="fa fa-ban"></i> Maaf, Terjadi kesalahan koneksi, silahkan muat ulang !!</div>',
                                timeout: 2000,
                                showType: 'slide'
                            });
                        }
                    });
                }
            });
        } else {
            $.messager.show({
                title: '<div><i class="fa fa-warning"></i> Peringatan !</div>',
                msg: '<div class="text-red"><i class="fa fa-ban"></i> Maaf, Data harus dipilih terlebih dahulu </div>',
                timeout: 2000,
                showType: 'slide'
            });
        }
        $('.messager-button a:last').focus();
    }


    function form_select_clear() {
        $('select option')
            .filter(function() {
                return !this.value || $.trim(this.value).length == 0;
            })
            .remove();
        $('select option')
            .first()
            .prop('selected', true);
    }

    function doSearch() {
        $('#dg').datagrid('load', {
            cari_status: $('#cari_status').val(),
            kode_transaksi: $('#kode_transaksi').val(),
            tgl_dari: $('input[name=daterangepicker_start]').val(),
            tgl_sampai: $('input[name=daterangepicker_end]').val()
        });
    }

    function clearSearch() {
        location.reload();
    }

    function pencairan_bunga() {
        var row = jQuery('#dg').datagrid('getSelected');
        if (row) {
            if (row.status === '<span class="label label-default">Cair</span>') {
                $.messager.show({
                    title: '<div><i class="fa fa-warning"></i> Peringatan !</div>',
                    msg: '<div class="text-red"><i class="fa fa-ban"></i> Maaf, Data deposito ini sudah dicairkan.</div>',
                    timeout: 2000,
                    showType: 'slide'
                });
                return false;
            }

            jQuery('#dialog-form-bunga').dialog('open').dialog('setTitle', 'Form Pencairan Bunga');
            jQuery('#form_bunga').form('clear');

            jQuery('#tgl_pencairan_bunga_txt').val('<?php echo $txt_tanggal; ?>');
            jQuery('#tgl_pencairan_bunga').val('<?php echo $tanggal; ?>');
            jQuery('#kas_id_bunga option[value="0"]').prop('selected', true);
            jQuery('#metode_pencairan option[value="Cash"]').prop('selected', true);

            $('#deposito_id_bunga').val(row.id);
            $('#anggota_bunga_txt').val(row.nama);

            // Fetch summary for dialog
            $.ajax({
                type: "POST",
                url: "<?php echo site_url('deposito/get_bunga_summary'); ?>",
                data: { id: row.id },
                success: function(result) {
                    var res = eval('(' + result + ')');
                    if (res.ok) {
                        $('#f_sum_bulan_berjalan').html(res.months_passed);
                        $('#f_sum_total_didapat').html(number_format(res.total_bunga_didapat));
                        $('#f_sum_bunga_per_bulan').html(number_format(res.bunga_per_bulan));
                        $('#f_sum_total_dicairkan').html(number_format(res.total_dicairkan));
                        $('#f_sum_bunga_tersedia').html(number_format(res.bunga_tersedia));

                        var default_bunga = Math.round(res.bunga_per_bulan);
                        if (res.bunga_tersedia < default_bunga) {
                            default_bunga = Math.round(res.bunga_tersedia);
                        }
                        if (default_bunga < 0) default_bunga = 0;
                        $('#jumlah_bunga').val(number_format(default_bunga));
                    }
                }
            });

        } else {
            $.messager.show({
                title: '<div><i class="fa fa-warning"></i> Peringatan !</div>',
                msg: '<div class="text-red"><i class="fa fa-ban"></i> Maaf, Data harus dipilih terlebih dahulu </div>',
                timeout: 2000,
                showType: 'slide'
            });
        }
    }

    function save_bunga() {
        var string = $("#form_bunga").serialize();
        var jumlah = $("#jumlah_bunga").val();
        if (jumlah <= 0 || jumlah == '') {
            $.messager.show({
                title: '<div><i class="fa fa-warning"></i> Peringatan ! </div>',
                msg: '<div class="text-red"><i class="fa fa-ban"></i> Maaf, Jumlah harus diisi.</div>',
                timeout: 2000,
                showType: 'slide'
            });
            $("#jumlah_bunga").focus();
            return false;
        }

        var kas = $("#kas_id_bunga").val();
        if (kas == 0) {
            $.messager.show({
                title: '<div><i class="fa fa-warning"></i> Peringatan ! </div>',
                msg: '<div class="text-red"><i class="fa fa-ban"></i> Maaf, Ambil dari Kas harus diisi.</div>',
                timeout: 2000,
                showType: 'slide'
            });
            $("#kas_id_bunga").focus();
            return false;
        }

        $.ajax({
            type: "POST",
            url: "<?php echo site_url('deposito/create_bunga'); ?>",
            data: string,
            success: function(result) {
                var result = eval('(' + result + ')');
                if (result.ok) {
                    $.messager.show({
                        title: '<div><i class="fa fa-info"></i> Informasi</div>',
                        msg: result.msg,
                        timeout: 2000,
                        showType: 'slide'
                    });
                    jQuery('#dialog-form-bunga').dialog('close');
                    $('#dg').datagrid('reload');
                } else {
                    $.messager.show({
                        title: '<div><i class="fa fa-warning"></i> Peringatan !</div>',
                        msg: result.msg,
                        timeout: 4000,
                        showType: 'slide'
                    });
                }
            }
        });
    }

    function history_bunga() {
        var row = jQuery('#dg').datagrid('getSelected');
        if (row) {
            jQuery('#dialog-history-bunga').dialog('open').dialog('setTitle', 'History Pencairan Bunga - ' + row.nama);

            // fetch summary
            $.ajax({
                type: "POST",
                url: "<?php echo site_url('deposito/get_bunga_summary'); ?>",
                data: {
                    id: row.id
                },
                success: function(result) {
                    var res = eval('(' + result + ')');
                    if (res.ok) {
                        $('#sum_bulan_berjalan').html(res.months_passed);
                        $('#sum_total_didapat').html(number_format(res.total_bunga_didapat));
                        $('#sum_bunga_per_bulan').html(number_format(res.bunga_per_bulan));
                        $('#sum_total_dicairkan').html(number_format(res.total_dicairkan));
                        $('#sum_bunga_tersedia').html(number_format(res.bunga_tersedia));
                    }
                }
            });

            // load datagrid history
            $('#dg_bunga').datagrid('load', {
                deposito_id: row.id
            });
        } else {
            $.messager.show({
                title: '<div><i class="fa fa-warning"></i> Peringatan !</div>',
                msg: '<div class="text-red"><i class="fa fa-ban"></i> Maaf, Data harus dipilih terlebih dahulu </div>',
                timeout: 2000,
                showType: 'slide'
            });
        }
    }
</script>