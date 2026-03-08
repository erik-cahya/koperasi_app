<style type="text/css">
    td,
    div {
        font-family: "Arial", "Helvetica", "sans-serif";
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
        font-family: "Source Sans Pro", "Arial", "Helvetica", "sans-serif";
        box-sizing: border-box;
    }

    .glyphicon {
        font-family: "Glyphicons Halflings"
    }
</style>

<?php
$tanggal = date('Y-m-d H:i');
$tanggal_arr = explode(' ', $tanggal);
$txt_tanggal = jin_date_ina($tanggal_arr[0]);
$txt_tanggal .= ' - ' . substr($tanggal_arr[1], 0, 5);
?>

<table id="dg" class="easyui-datagrid" title="Data Tabungan Berjangka" style="width:auto; height: auto;"
    url="<?php echo site_url('berjangka/ajax_list'); ?>"
    pagination="true" rownumbers="true" fitColumns="true" singleSelect="true" collapsible="true"
    sortName="tgl_daftar" sortOrder="DESC" toolbar="#tb" striped="true">
    <thead>
        <tr>
            <th data-options="field:'id',halign:'center', align:'center'" hidden="true">ID</th>
            <th data-options="field:'id_txt', width:'15', halign:'center', align:'center'">Kode</th>
            <th data-options="field:'tgl_daftar', halign:'center', align:'center'" hidden="true">Tanggal</th>
            <th data-options="field:'tgl_daftar_txt', width:'25', halign:'center', align:'center'">Tanggal <br> Daftar</th>
            <th data-options="field:'anggota_id',halign:'center', align:'center'" hidden="true">ID</th>
            <th data-options="field:'anggota_id_txt', width:'15', halign:'center', align:'center'">ID Anggota</th>
            <th data-options="field:'nama', width:'25', halign:'center', align:'left'">Nama Anggota</th>
            <th data-options="field:'setoran_per_bulan', width:'20', halign:'center', align:'right'">Setoran (Bln)</th>
            <th data-options="field:'lama_bulan', width:'15', halign:'center', align:'center'">Lama Waktu</th>
            <th data-options="field:'bunga', width:'10', halign:'center', align:'center'">Bunga</th>
            <th data-options="field:'tgl_jatuh_tempo_txt', width:'20', halign:'center', align:'center'">Jatuh Tempo</th>
            <th data-options="field:'total_terkumpul', width:'20', halign:'center', align:'right'">Total Tersimpan</th>
            <th data-options="field:'estimasi_bunga', width:'20', halign:'center', align:'right'">Estimasi Bunga</th>
            <th data-options="field:'total_kembali', width:'20', halign:'center', align:'right'">Total Pencairan</th>
            <th data-options="field:'status', width:'15', halign:'center', align:'center'">Status</th>
            <th data-options="field:'ket', width:'20', halign:'center', align:'left'">Keterangan</th>
            <th data-options="field:'user', width:'15', halign:'center', align:'center'">User</th>
        </tr>
    </thead>
</table>

<div id="tb" style="height: 35px;">
    <div style="vertical-align: middle; display: inline; padding-top: 15px;">
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="create()">Daftar Tabungan</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-redo" plain="true" onclick="setor()">Setor Bulanan</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" plain="true" onclick="pencairan()">Pencairan</a>
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
            <option value="Selesai">Selesai/Cair</option>
        </select>
        <span>Cari :</span>
        <input name="kode_transaksi" id="kode_transaksi" size="23" placeholder="Kode Transaksi/ID" style="line-height:22px;border:1px solid #ccc">
        <a href="javascript:void(0);" iconCls="icon-search" class="easyui-linkbutton" onclick="doSearch()">Cari</a>
        <a href="javascript:void(0);" iconCls="icon-clear" class="easyui-linkbutton" onclick="clearSearch()">Bersihkan</a>
    </div>
</div>

<!-- Form Daftar Baru -->
<div id="dialog-form" class="easyui-dialog" show="blind" hide="blind" modal="true" resizable="false" style="width:500px; height:450px; padding: 20px 15px;" closed="true" buttons="#dialog-buttons">
    <form id="form" method="post" novalidate>
        <table>
            <tr>
                <td>
                    <table>
                        <tr style="height:35px">
                            <td>Tanggal Daftar</td>
                            <td>:</td>
                            <td>
                                <div class="input-group date dtpicker col-md-5" style="z-index: 9999 !important;">
                                    <input type="text" name="tgl_daftar_txt" id="tgl_daftar_txt" style=" background:#eee; width:155px; height:23px" required="true" readonly="readonly" />
                                    <input type="hidden" name="tgl_daftar" id="tgl_daftar" />
                                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                </div>
                            </td>
                        </tr>
                        <tr style="height:35px">
                            <td>Nama Anggota</td>
                            <td>:</td>
                            <td><input id="anggota_id" name="anggota_id" style="width:195px; height:25px" class="easyui-validatebox" required="true"></td>
                        </tr>
                        <tr style="height:35px">
                            <td>Setoran Per Bulan</td>
                            <td>:</td>
                            <td><input class="" id="setoran_per_bulan" name="setoran_per_bulan" style="width:195px; height:25px; " required="true" /></td>
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
                            <td><input type="text" id="bunga" name="bunga" style="width:195px; height:23px" required="true" /></td>
                        </tr>
                        <tr style="height:35px">
                            <td>Kas Penerima</td>
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
                            <td><input id="ket" name="ket" style="width:190px; height:20px"></td>
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
<div id="dialog-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="save()">Simpan</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:jQuery('#dialog-form').dialog('close')">Batal</a>
</div>

<!-- Form Setoran Rutin -->
<div id="dialog-setor-form" class="easyui-dialog" show="blind" hide="blind" modal="true" resizable="false" style="width:400px; height:320px; padding: 20px 15px;" closed="true" buttons="#dialog-setor-buttons">
    <form id="form_setor" method="post" novalidate>
        <input type="hidden" name="berjangka_id" id="berjangka_id">
        <table>
            <tr style="height:35px">
                <td>Tanggal Setor</td>
                <td>:</td>
                <td>
                    <div class="input-group date dtpicker col-md-5" style="z-index: 9999 !important;">
                        <input type="text" name="tgl_transaksi_txt" id="tgl_transaksi_txt" style=" background:#eee; width:155px; height:23px" required="true" readonly="readonly" />
                        <input type="hidden" name="tgl_transaksi" id="tgl_transaksi" />
                        <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                    </div>
                </td>
            </tr>
            <tr style="height:35px">
                <td>Jumlah Setor</td>
                <td>:</td>
                <td><input class="" id="jumlah_setor" name="jumlah_setor" style="width:195px; height:25px;" required="true" /></td>
            </tr>
            <tr style="height:35px">
                <td>Pilih Kas (Tujuan)</td>
                <td>:</td>
                <td>
                    <select id="kas_id_setor" name="kas_id_setor" style="width:200px; height:23px" class="easyui-validatebox" required="true">
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
                <td><input id="ket_setor" name="ket_setor" style="width:190px; height:20px"></td>
            </tr>
        </table>
    </form>
</div>
<div id="dialog-setor-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="save_setor()">Setor</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:jQuery('#dialog-setor-form').dialog('close')">Batal</a>
</div>

<!-- Form History Setoran -->
<div id="dialog-history-form" class="easyui-dialog" show="blind" hide="blind" modal="true" resizable="false" style="width:600px; height:400px; padding: 10px;" closed="true">
    <table id="dg_history" class="easyui-datagrid" style="width:auto; height: 340px;"
        pagination="true" rownumbers="true" fitColumns="true" singleSelect="true" striped="true">
        <thead>
            <tr>
                <th data-options="field:'tgl_transaksi_txt', width:'35', halign:'center', align:'center'">Tanggal <br> Transaksi</th>
                <th data-options="field:'jumlah', width:'30', halign:'center', align:'right'">Jumlah</th>
                <th data-options="field:'keterangan', width:'40', halign:'center', align:'left'">Keterangan</th>
                <th data-options="field:'user_name', width:'20', halign:'center', align:'center'">User</th>
            </tr>
        </thead>
    </table>
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
            linkField: "tgl_daftar",
            linkFormat: "yyyy-mm-dd hh:ii"
        });

        $(".dtpicker").on('changeDate', function(ev) {
            if (ev.target.childNodes[1].name == "tgl_transaksi_txt") {
                $("#tgl_transaksi").val(
                    ev.date.getFullYear() + "-" +
                    ("0" + (ev.date.getMonth() + 1)).slice(-2) + "-" +
                    ("0" + ev.date.getDate()).slice(-2) + " " +
                    ("0" + ev.date.getHours()).slice(-2) + ":" +
                    ("0" + ev.date.getMinutes()).slice(-2) + ":00"
                );
            }
        });

        $('#anggota_id').combogrid({
            panelWidth: 400,
            url: '<?php echo site_url('berjangka/list_anggota'); ?>',
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
                var val_anggota_id = $('#anggota_id').combogrid('getValue');
                $.ajax({
                    url: '<?php echo site_url(); ?>berjangka/get_anggota_by_id',
                    type: 'POST',
                    dataType: 'html',
                    data: {
                        anggota_id: val_anggota_id
                    },
                }).done(function(result) {
                    $('#anggota_poto').html(result);
                }).fail(function() {
                    alert('Koneksi error, silahkan ulangi.')
                });
            }
        });

        $("#cari_status").change(function() {
            $('#dg').datagrid('load', {
                cari_status: $('#cari_status').val()
            });
        });
        $("#kode_transaksi").keyup(function(e) {
            if (e.keyCode == 13) doSearch();
            var isi = $(e.target).val();
            $(e.target).val(isi.toUpperCase());
        });
        $('#setoran_per_bulan').keyup(function() {
            $('#setoran_per_bulan').val(number_format($(this).val()));
        });
        $('#jumlah_setor').keyup(function() {
            $('#jumlah_setor').val(number_format($(this).val()));
        });
        fm_filter_tgl();
    });

    function fm_filter_tgl() {
        $('#daterange-btn').daterangepicker({
            ranges: {
                'Tahun ini': [moment().startOf('year').startOf('month'), moment().endOf('year').endOf('month')]
            },
            showDropdowns: true,
            format: 'YYYY-MM-DD',
            startDate: moment().startOf('year').startOf('month'),
            endDate: moment().endOf('year').endOf('month')
        }, function(start, end) {
            $('#reportrange span').html(start.format('D MMM YYYY') + ' - ' + end.format('D MMM YYYY'));
            doSearch();
        });
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

    function create() {
        $('#dialog-form').dialog('open').dialog('setTitle', 'Pendaftaran Tabungan Berjangka');
        $('#form').form('clear');
        $('#tgl_daftar_txt').val('<?php echo $txt_tanggal; ?>');
        $('#tgl_daftar').val('<?php echo $tanggal; ?>');
        $('#kas option[value="0"]').prop('selected', true);
        $('#lama_bulan option[value="0"]').prop('selected', true);
        $("#anggota_poto").html('');
    }

    function save() {
        var string = $("#form").serialize();
        var anggota_id = $('#anggota_id').combogrid('getValue');
        if (!anggota_id) {
            showError('Maaf, Anggota belum dipilih.');
            return false;
        }
        if ($("#setoran_per_bulan").val() <= 0) {
            showError('Maaf, Setoran Per Bulan harus diisi.');
            return false;
        }
        if ($("#lama_bulan").val() == 0) {
            showError('Maaf, Lama Waktu belum dipilih.');
            return false;
        }
        if ($("#kas").val() == 0) {
            showError('Maaf, Ambil dari Kas harus diisi.');
            return false;
        }

        $.ajax({
            type: "POST",
            url: '<?php echo site_url('berjangka/create'); ?>',
            data: string,
            success: function(result) {
                var res = eval('(' + result + ')');
                if (res.ok) {
                    $('#dialog-form').dialog('close');
                    $('#dg').datagrid('reload');
                }
                $.messager.show({
                    title: 'Informasi',
                    msg: res.msg,
                    timeout: 2000,
                    showType: 'slide'
                });
            }
        });
    }

    function setor() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            if (row.status.indexOf('Selesai') !== -1) {
                showError('Kontrak sudah Selesai/Cair.');
                return false;
            }
            $('#dialog-setor-form').dialog('open').dialog('setTitle', 'Setor Tabungan Berjangka - ' + row.nama);
            $('#form_setor').form('clear');
            $('#berjangka_id').val(row.id);
            $('#jumlah_setor').val(row.setoran_per_bulan);
            $('#tgl_transaksi_txt').val('<?php echo $txt_tanggal; ?>');
            $('#tgl_transaksi').val('<?php echo $tanggal; ?>');
            $('#kas_id_setor option[value="0"]').prop('selected', true);
        } else {
            showError('Pilih data tabungan berjangka pada list/tabel terlebih dahulu.');
        }
    }

    function save_setor() {
        var string = $("#form_setor").serialize();
        if ($("#jumlah_setor").val() <= 0) {
            showError('Maaf, Jumlah Setor harus diisi.');
            return false;
        }
        if ($("#kas_id_setor").val() == 0) {
            showError('Maaf, Kas penerima harus dipilih.');
            return false;
        }

        $.ajax({
            type: "POST",
            url: '<?php echo site_url('berjangka/setor'); ?>',
            data: string,
            success: function(result) {
                var res = eval('(' + result + ')');
                if (res.ok) {
                    $('#dialog-setor-form').dialog('close');
                    $('#dg').datagrid('reload');
                }
                $.messager.show({
                    title: 'Informasi',
                    msg: res.msg,
                    timeout: 2000,
                    showType: 'slide'
                });
            }
        });
    }

    function pencairan() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            if (row.status.indexOf('Selesai') !== -1) {
                showError('Tabungan sudah dicairkan.');
                return false;
            }
            $.messager.confirm('Konfirmasi', 'Apakah anda yakin mencairkan tabungan <code>' + row.id_txt + '</code> ?', function(r) {
                if (r) {
                    $.ajax({
                        type: "POST",
                        url: "<?php echo site_url('berjangka/pencairan'); ?>",
                        data: {
                            id: row.id
                        },
                        success: function(result) {
                            var res = eval('(' + result + ')');
                            if (res.ok) $('#dg').datagrid('reload');
                            $.messager.show({
                                title: 'Informasi',
                                msg: res.msg,
                                timeout: 2000,
                                showType: 'slide'
                            });
                        }
                    });
                }
            });
        } else {
            showError('Pilih data terlebih dahulu.');
        }
    }

    function hapus() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $.messager.confirm('Konfirmasi', 'Menghapus kontrak tabungan berjangka beserta seluruh history setorannya?', function(r) {
                if (r) {
                    $.ajax({
                        type: "POST",
                        url: "<?php echo site_url('berjangka/delete'); ?>",
                        data: {
                            id: row.id
                        },
                        success: function(result) {
                            var res = eval('(' + result + ')');
                            if (res.ok) $('#dg').datagrid('reload');
                            $.messager.show({
                                title: 'Informasi',
                                msg: res.msg,
                                timeout: 2000,
                                showType: 'slide'
                            });
                        }
                    });
                }
            });
        } else {
            showError('Pilih data terlebih dahulu.');
        }
    }

    function show_history_setor(berjangka_id, nama_anggota) {
        $('#dialog-history-form').dialog('open').dialog('setTitle', 'History Setoran - ' + nama_anggota);
        $('#dg_history').datagrid({
            url: '<?php echo site_url("berjangka/ajax_list_setor"); ?>/' + berjangka_id
        });
        $('#dg_history').datagrid('load');
    }

    function showError(msg) {
        $.messager.show({
            title: 'Peringatan !',
            msg: '<div class="text-red"><i class="fa fa-ban"></i> ' + msg + '</div>',
            timeout: 2000,
            showType: 'slide'
        });
    }
</script>