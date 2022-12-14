/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */
$(window).ready(() => {
    var $admissions_enabled = $('#rg_ocaepak_registration_RG_OCAEPAK_ADMISSIONS_ENABLED_1');
    var $pickups_enabled = $('#rg_ocaepak_registration_RG_OCAEPAK_PICKUPS_ENABLED_1');
    var $admissions_disabled= $('#rg_ocaepak_registration_RG_OCAEPAK_ADMISSIONS_ENABLED_0');
    var $pickups_disabled = $('#rg_ocaepak_registration_RG_OCAEPAK_PICKUPS_ENABLED_0');
    var $admission_settings = $('#branch-admission-card');
    var $pickup_settings = $('#pickup-settings-card');
    var $boxes_card = $('#boxes-card');
    var $add_box = $('#add-oca-box');
    var $boxnum = parseInt($boxes_card.attr('data-number-boxes'));

    $boxes_card.hide();
    console.log($admission_settings.attr('show'));
    if($admission_settings.attr('show')!=='1'){
        hideAdmissions();
    }else {
        showAdmissions();
    }
    if($pickup_settings.attr('show')!=='1'){
       hidePickups();
    }else {
        showPickups();
    }

    $admissions_enabled.on('change', ()=>{
        showAdmissions();
    });

    $admissions_disabled.on('change', ()=>{
        hideAdmissions();
    });

    $pickups_enabled.on('change', ()=>{
        showPickups();
    });

    $pickups_disabled.on('change', ()=>{
        hidePickups();
    });

    function showAdmissions(){
        console.log('Show admissions');
        $admission_settings.show();
        $admission_settings.find('#rg_ocaepak_registration_RG_OCAEPAK_ADMISSION_BRANCH').attr('required','required');
        $boxes_card.show();
    }

    function hideAdmissions(){
        console.log('Hide admissions');
        $admission_settings.hide();
        if($pickup_settings.is(":hidden")){
            $boxes_card.hide();
        }
        $admission_settings.find('#rg_ocaepak_registration_RG_OCAEPAK_ADMISSION_BRANCH').attr('required',false);
    }

    function showPickups(){
        console.log('Show pickups');
        $pickup_settings.show();
        $boxes_card.show();
        $pickup_settings.find('#rg_ocaepak_registration_RG_OCAEPAK_STREET').attr('required','required');
        $pickup_settings.find('#rg_ocaepak_registration_RG_OCAEPAK_NUMBER').attr('required','required');
        $pickup_settings.find('#rg_ocaepak_registration_RG_OCAEPAK_LOCALITY').attr('required','required');
        $pickup_settings.find('#rg_ocaepak_registration_RG_OCAEPAK_PROVINCE').attr('required','required');
    }

    function hidePickups(){
        console.log('Hide pickups');
        $pickup_settings.hide();
        if($admission_settings.is(":hidden")){
            $boxes_card.hide();
        }
        $pickup_settings.find('#rg_ocaepak_registration_RG_OCAEPAK_STREET').attr('required',false);
        $pickup_settings.find('#rg_ocaepak_registration_RG_OCAEPAK_NUMBER').attr('required',false);
        $pickup_settings.find('#rg_ocaepak_registration_RG_OCAEPAK_LOCALITY').attr('required',false);
        $pickup_settings.find('#rg_ocaepak_registration_RG_OCAEPAK_PROVINCE').attr('required',false);
    }

    $add_box.on('click', function (){
        $boxnum += 1;
        var $newbox = $('#oca-box-1').clone().attr('id', 'oca-box-'+$boxnum);
        $newbox.find('input').each(function(){
            var split = $(this).attr('name').lastIndexOf('-')+1;
            $(this).attr('name', $(this).attr('name').substr(0,split)+$boxnum);
            $(this).attr('id', $(this).attr('id').substr(0,split)+$boxnum);
            $(this).removeAttr('checked');
        });
        $newbox.find('.card-header').find('label').text("Box "+$boxnum);
        $newbox.find('.card-header').find('a').attr('href',$newbox.find('.card-header').find('a').attr('href').slice(0,-1)+$boxnum);
        $('#add-oca-box').before($newbox);
    })

    $(document).on('click','.card-header-pills', function (){
        var $id = $(this).attr('href').slice(-1);
        if($boxnum=$id) {
            $boxnum -= 1;
        }
        $('#oca-box-'+$id).remove();
    })

    $(document).on('click','.oca-checkbox-field', function (){
       if($(this).is(':checked')==true){
           var $id = $(this).attr('id');
           $(document).find('input[type=checkbox]').each(function (){
            if($(this).attr('id')!=$id){
                $(this).removeAttr('checked');
            }
           });
       }else{
           $(this).attr('checked',true);
           alert('Tienes que seleccionar alguna caja');

       }
    });


});
