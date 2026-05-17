document.addEventListener("DOMContentLoaded", function(){
    const chartEl = document.querySelector("#chartDetallado");

    if(!chartEl) return;

    const options = {
        chart: { type: 'bar', height: 300, animations:{enabled:true,dynamicAnimation:{speed:500}} },
        series: [{ name:'Energia Wh', data: window.datosDetallado.map(d=>d.valor) }],
        xaxis: { categories: window.datosDetallado.map(d=>d.hora) },
        yaxis: { min:0, max:2 },
        dataLabels:{enabled:true,formatter:(val)=>val.toFixed(2)},
        tooltip:{y:{formatter:(val)=>val.toFixed(2)+' Wh'}}
    };

    const chart = new ApexCharts(chartEl, options);
    chart.render();

    // Actualización cada 10s
    setInterval(async ()=>{
        try{
            const res = await fetch(`/public/usuarios/getUltimosRegistros.php?id_modulo=${window.id_modulo}&limite=20`);
            const data = await res.json();
            if(data.datos){
                chart.updateOptions({
                    series:[{name:'Energia Wh', data:data.datos.map(d=>d.valor)}],
                    xaxis:{categories:data.datos.map(d=>d.hora)}
                });
            }
        }catch(e){ console.error("Error actualizando datos:",e); }
    },10000);
});






