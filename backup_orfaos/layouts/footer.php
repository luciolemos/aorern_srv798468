<?php
// Define tipo de rodapé para a componente
$footer_type = 'public';
require __DIR__ . '/../components/footer.php';
?>

<!-- JS e Scripts finais -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init();</script>

<!-- Mermaid.js -->
<script type="module">
    import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.esm.min.mjs';
    mermaid.initialize({ startOnLoad: true });
</script>

<!-- Copiar comandos -->
<script>
    function copiarComandos() {
        const texto = document.getElementById("blocoComandos").innerText;
        navigator.clipboard.writeText(texto).then(() => {
            alert("Comandos copiados!");
        }).catch(err => {
            alert("Erro ao copiar: " + err);
        });
    }
</script>

</body>


</html>
