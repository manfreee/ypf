const ctx = document.getElementById('miGrafico').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Tensión (V)',
                        data: [],
                        borderColor: 'red',
                        borderWidth: 1.5,
                        tension: 0.2, // suaviza las curvas
                        fill: false
                    },
                    {
                        label: 'Corriente (A)',
                        data: [],
                        borderColor: 'blue',
                        borderWidth: 1.5,
                        tension: 0.2,
                        fill: false
                    },
                    {
                        label: 'Potencia (W)',
                        data: [],
                        borderColor: 'green',
                        borderWidth: 1.5,
                        tension: 0.2,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // permite ajustar altura manual
                animation: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { size: 11 } }
                    }
                },
                scales: {
                    x: {
                        title: { display: true, text: 'Fecha/Hora', font: { size: 12 } },
                        ticks: { maxRotation: 45, minRotation: 45, font: { size: 10 } }
                    },
                    y: {
                        title: { display: true, text: 'Valor', font: { size: 12 } },
                        ticks: { font: { size: 10 } }
                    }
                }
            }
        });

        const intervaloActualizacion = 5; // segundos para actualizar el grafico
        let tiempoRestante = intervaloActualizacion;

        async function actualizarDatos() {
            try {
                const respuesta = await fetch('PHP/datos_json.php');
                const datos = await respuesta.json();

                chart.data.labels = datos.map(d => d.fecha_hora);
                chart.data.datasets[0].data = datos.map(d => d.tension);
                chart.data.datasets[1].data = datos.map(d => d.corriente);
                chart.data.datasets[2].data = datos.map(d => d.potencia);
                chart.update();
            } catch (error) {
                console.error('Error al actualizar datos:', error);
            }
        }

        function iniciarContador() {
            const spanTiempo = document.getElementById('tiempo');
            setInterval(() => {
                tiempoRestante--;
                if (tiempoRestante <= 0) {
                    actualizarDatos();
                    tiempoRestante = intervaloActualizacion;
                }
                spanTiempo.textContent = tiempoRestante;
            }, 1000);
        }

        actualizarDatos();
        iniciarContador();