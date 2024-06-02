/*
import './bootstrap.js';
*/
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import '../styles/app.scss';

import $ from 'jquery';
global.$ = global.jQuery = $;

export function initDataTable(selector, loaderSelector){
    // DATATABLE
    $(selector).DataTable({
        "lengthMenu": [[10, 25, 50, 100, 500, -1], [10, 25, 50, 100, 500, "Tous"]],
        "language": {
            "sProcessing": "Traitement en cours...",
            "sSearch": "Rechercher&nbsp; :",
            "sLengthMenu": "_MENU_",
            "sInfo": "Affichage des éléments _START_ jusqu'a _END_ sur le total de _TOTAL_ éléments",
            "sInfoEmpty": "Affichage des éléments correspondants à la recherche",
            "sInfoFiltered": "(filtré sur l'ensemble des _MAX_ éléments au total)",
            "sInfoPostFix": "",
            "sLoadingRecords": "Chargement en cours...",
            "sZeroRecords": "Aucun élément à afficher",
            "sEmptyTable": "Aucune donnée n'est disponible pour ce tableau",
            "oPaginate": {
                "sFirst": "Premier",
                "sPrevious": "<",
                "sNext": ">",
                "sLast": "Dernier"
            },
            "oAria": {
                "sSortAscending": ": activer pour trier la colonne par ordre croissant",
                "sSortDescending": ": activer pour trier la colonne par ordre d&eacute;croissant"
            }
        },
        "scrollX": true,
        "initComplete": function() {
            $(loaderSelector).addClass('hidden');
            $(selector).removeClass('hidden');
        },
        "aaSorting": [],
        "oSearch": {"sSearch": $('#searchValue').val() },
        "deferRender": true,
        "pageLength": 10
    });
}