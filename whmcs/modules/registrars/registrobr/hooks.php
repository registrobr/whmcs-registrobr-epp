<?php

add_hook('ClientAreaFooterOutput', 1, function ($domain) {
    if (strpos($domain['currentpagelinkback'], 'cart.php?a=confdomains') !== false) {
        // Formats the additionalfields (CPF ou CNPJ) in the client area page "/cart.php?a=confdomains".
        echo <<<HTML
        <script type="text/javascript">
            window.addEventListener("DOMContentLoaded", (event) => {
                const docInput = document.getElementById('cpf-cnpj-rgbr-formatter').parentElement.firstChild

                if (docInput) {
                    docInput.maxLength = 18
                    docInput.minLength = 14

                    docInput.addEventListener('input', e => {
                        // Source: https://gist.github.com/marceloneppel/dd9c17a01c1a8031c760b034dad0efd9
                        const rawValue = e.target.value.replace(/\D/g, '')

                        if (rawValue.length >= 11) {
                            if (rawValue.length === 11) {
                                e.target.value = rawValue.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/g, "\$1.\$2.\$3-\$4")

                                return
                            }

                            e.target.value = rawValue.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/g, "\$1.\$2.\$3/\$4-\$5")

                            return
                        } else {
                            e.target.value = rawValue
                        }
                    })
                }
            });
        </script>
HTML;
    }
});
