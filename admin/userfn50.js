
// Global user functions
function initimagecropper(width, height, folder, field, img, apppathfromadmin, preserveoriginalimage, originalimagemaxwidth, originalimagetargetfield ,  originalimagemaxheight) {
    preserveoriginalimage = preserveoriginalimage==null ? 0 : preserveoriginalimage?1:0;
    originalimagemaxwidth = originalimagemaxwidth==null ? "" : originalimagemaxwidth;
    originalimagemaxwidth = originalimagemaxheight==null ? "" : originalimagemaxheight;
    originalimagetargetfield = originalimagetargetfield==null ? "" : originalimagetargetfield;
    window.open('cropper/index.php?targetfolder='+folder+'&targetfield='+field+'&targetwidth='+width+'&targetheight='+height+'&targetimg='+img+'&apppathfromadmin='+apppathfromadmin+'&preserveoriginalimage='+preserveoriginalimage+'&originalimagemaxwidth='+originalimagemaxwidth+'&originalimagetargetfield='+originalimagetargetfield + '&originalimagemaxheight='+originalimagemaxheight,'cropperpopup','width=960,height=550,scrollbars=yes,menu=no,resizable=yes');
}

// Global user functions
