<style>
  table{
    border: 1px solid #000;
    text-align: center;
  }
</style>
<table>
  <tr>
    @foreach($xlsxDataHeader as $key => $value)
      @if ($value !== false)
        <td style="text-align: center; border: 1px solid #000000;width: 15px;">{{ $value }}</td>
      @else
        <td></td>
      @endif
    @endforeach
  </tr>
  @foreach($xlsxDataBody as $key => $xlsxDataRow)
    <tr>
    @foreach($xlsxDataRow as $key2 => $value)
      @if ($value !== false)
        <td style="text-align: center; border: 1px solid #000000;width: 15px;">{{ $value }}</td>
      @else
        <td></td>
      @endif
    @endforeach
    </tr>
  @endforeach
</table>
