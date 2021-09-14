<style>
  table{
    border: 1px solid #000;
    text-align: center;
  }
</style>
<table>
  <tr>
    <th colspan="3" style="text-align: center; border: 1px solid #000000;">マスタデータ</th>
    <th></th>
    <th colspan="3" style="text-align: center; border: 1px solid #000000;">クライアントデータ</th>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td colspan="2"  style="text-align: center; border: 1px solid #000000;">登録名</td>
    <td style="text-align: center; border: 1px solid #000000; width:15px;">{{ $exportHeaderM['registeredName'] }}</td>
    <td></td>
    <td colspan="2"  style="text-align: center; border: 1px solid #000000;">登録名</td>
    <td style="text-align: center; border: 1px solid #000000; width:15px;">{{ $exportHeaderC['registeredName'] }}</td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td colspan="2" style="text-align: center; border: 1px solid #000000;">入庫台数</td>
    <td  style="text-align: center; border: 1px solid #000000;">{{ $exportHeaderM['registeredAmount'] }}</td>
    <td></td>
    <td colspan="2" style="text-align: center; border: 1px solid #000000;">入庫台数</td>
    <td  style="text-align: center; border: 1px solid #000000;">{{ $exportHeaderC['registeredAmount'] }}</td>
    <td></td>
    <td colspan="7" style="text-align: center; border: 1px solid #000000;">結果</td>
  </tr>
  <tr>
    <td style="text-align: center; border: 1px solid #000000; width:20px;">品名</td>
    <td style="text-align: center; border: 1px solid #000000;">個数</td>
    <td style="text-align: center; border: 1px solid #000000;">個数/入庫台数</td>
    <td></td>
    <td style="text-align: center; border: 1px solid #000000; width:20px;">品名</td>
    <td style="text-align: center; border: 1px solid #000000;">個数</td>
    <td style="text-align: center; border: 1px solid #000000;">個数/入庫台数</td>
    <td></td>
    <td style="text-align: center; border: 1px solid #000000; width:20px;">品名</td>
    <td style="text-align: center; border: 1px solid #000000;">差分</td>
    <td style="text-align: center; border: 1px solid #000000;">台数換算</td>
    <td style="text-align: center; border: 1px solid #000000;">部品価格</td>
    <td style="text-align: center; border: 1px solid #000000;">機会損失部品</td>
    <td style="text-align: center; border: 1px solid #000000;">工賃単価</td>
    <td style="text-align: center; border: 1px solid #000000;">機会損失工賃</td>
  </tr>
  @foreach($xlsxData as $key => $row)
  <tr>
    <td style="text-align: center; border: 1px solid #000000;">{{ $row['product_name'] }}</td>
    <td style="text-align: center; border: 1px solid #000000;">{{ $row['product_amount'] }}</td>
    <td style="text-align: center; border: 1px solid #000000;">{{ $row['amt_by_nums'] }}</td>
    <td></td>
    <td style="text-align: center; border: 1px solid #000000;">{{ $row['product_name_c'] }}</td>
    <td style="text-align: center; border: 1px solid #000000;">{{ $row['product_amount_c'] }}</td>
    <td style="text-align: center; border: 1px solid #000000;">{{ $row['amt_by_nums_c'] }}</td>
    <td></td>
    <td style="text-align: center; border: 1px solid #000000;">{{ $row['diffs_name'] }}</td>
    <td style="text-align: center; border: 1px solid #000000;">{{ $row['diffs'] }}</td>
    <td style="text-align: center; border: 1px solid #000000;">{{ $row['diffs_to_nums'] }}</td>
    <td style="text-align: center; border: 1px solid #000000;"></td>
    <td style="text-align: center; border: 1px solid #000000;"></td>
    <td style="text-align: center; border: 1px solid #000000;"></td>
    <td style="text-align: center; border: 1px solid #000000;"></td>
  </tr>
  @endforeach
</table>
