    <tr>
        <td class="iconcell" rowspan="2">
            {$formcontrols|safe}
        </td>
        <th><label for="{$elementname}_{$artefact->id}" title="{$artefact->title|strip_tags|str_shorten_text:60:true|safe}">
            {$artefact->title|str_shorten_html:100:true|strip_tags|safe}
            </label>
        </th>
    </tr>
    <tr>

    </tr>
