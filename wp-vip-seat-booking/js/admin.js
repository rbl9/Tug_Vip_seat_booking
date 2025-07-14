(function(){
    document.addEventListener('DOMContentLoaded',function(){
        const wrap = document.getElementById('vip-booking-admin');
        if(!wrap) return;
        wp.apiFetch({path:VIPBooking.apiUrl+'/bookings'}).then(bookings=>{
            wrap.innerHTML = '<pre>'+JSON.stringify(bookings,null,2)+'</pre>';
        });
    });
})();
