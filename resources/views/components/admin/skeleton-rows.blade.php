{{--
    Loading placeholder rows for tables: <x-admin.skeleton-rows :cols="6" :rows="5" />
--}}
@props(['cols' => 4, 'rows' => 5])
@for ($r = 0; $r < $rows; $r++)
    <tr aria-hidden="true">
        @for ($c = 0; $c < $cols; $c++)
            <td><span class="a-skeleton" style="width: {{ [70, 55, 85, 40, 60][($r + $c) % 5] }}%"></span></td>
        @endfor
    </tr>
@endfor
