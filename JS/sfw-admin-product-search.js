(function($) {
    let timer = null;
    let selectedProducts = [];
    let $input, $results, $selected, $hiddenInput;

    function renderSelected() {
        $selected.empty();
        selectedProducts.forEach(function(prod) {
            $selected.append(
                $('<div class="sfw-selected-product">').text(prod.text + ' (ID: ' + prod.id + ')')
                    .append(
                        $('<span class="sfw-remove">&times;</span>').click(function() {
                            selectedProducts = selectedProducts.filter(p => p.id !== prod.id);
                            updateHiddenInput();
                            renderSelected();
                        })
                    )
            );
        });
    }

    function updateHiddenInput() {
        $hiddenInput.val(selectedProducts.map(p => p.id).join(','));
    }

    function searchProducts(term) {
        if (!term) {
            $results.empty();
            return;
        }
        $results.html('<div>Searching...</div>');
        $.ajax({
            url: sfwProductSearch.ajax_url,
            method: 'POST',
            data: {
                action: 'sfw_product_search',
                nonce: sfwProductSearch.nonce,
                term: term
            },
            success: function(res) {
                $results.empty();
                if (res.success && res.data.length) {
                    res.data.forEach(function(prod) {
                        $results.append(
                            $('<div class="sfw-search-result">').text(prod.text + ' (ID: ' + prod.id + ')')
                                .click(function() {
                                    if (!selectedProducts.find(p => p.id === prod.id)) {
                                        selectedProducts.push(prod);
                                        updateHiddenInput();
                                        renderSelected();
                                    }
                                    $results.empty();
                                    $input.val('');
                                })
                        );
                    });
                } else {
                    $results.html('<div>No products found.</div>');
                }
            },
            error: function() {
                $results.html('<div>Error searching products.</div>');
            }
        });
    }

    $(function() {
        $input = $('#sfw_product_search_input');
        $results = $('#sfw_product_search_results');
        $selected = $('#sfw_selected_products');
        $hiddenInput = $('#sfw_hidden_product_ids');

        // Load initial selected products from hidden input
        let initialIds = $hiddenInput.val().split(',').map(id => id.trim()).filter(Boolean);
        if (initialIds.length) {
            // Fetch product titles for initial IDs
            $.ajax({
                url: sfwProductSearch.ajax_url,
                method: 'POST',
                data: {
                    action: 'sfw_product_search',
                    nonce: sfwProductSearch.nonce,
                    ids: initialIds.join(',')
                },
                success: function(res) {
                    if (res.success && res.data.length) {
                        selectedProducts = res.data;
                        renderSelected();
                    }
                }
            });
        }

        $input.on('input', function() {
            clearTimeout(timer);
            let term = $(this).val();
            timer = setTimeout(function() {
                searchProducts(term);
            }, 1000);
        });

        $input.on('keydown', function(e) {
            if (e.key === 'Enter' && $results.children().length) {
                $results.children().first().click();
                e.preventDefault();
            }
        });
    });
})(jQuery); 