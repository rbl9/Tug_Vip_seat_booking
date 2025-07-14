(function(){
    const e = React.createElement;

    function BookingApp(){
        const [dates,setDates] = React.useState([]);
        const [name,setName] = React.useState('');
        const [phone,setPhone] = React.useState('');
        const submit = () => {
            wp.apiFetch({
                path: VIPBooking.apiUrl+'/bookings',
                method:'POST',
                data:{name,phone,dates},
                headers:{'X-WP-Nonce':VIPBooking.nonce}
            }).then(r=>{alert('booking saved');}).catch(err=>alert('error'));
        };
        return e('div',null,
            e('div',{id:'datepicker'}),
            e('input',{type:'text',placeholder:'نام و نام خانوادگی',value:name,onChange:e=>setName(e.target.value)}),
            e('input',{type:'text',placeholder:'شماره تماس',value:phone,onChange:e=>setPhone(e.target.value)}),
            e('button',{onClick:submit},'ثبت رزرو')
        );
    }

    document.addEventListener('DOMContentLoaded',function(){
        ReactDOM.render(e(BookingApp),document.getElementById('vip-booking-app'));
    });
})();
