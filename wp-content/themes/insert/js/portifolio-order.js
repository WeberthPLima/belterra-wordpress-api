( function( wp ) {
    var registerPlugin = wp.plugins.registerPlugin;
    var PluginDocumentSettingPanel = wp.editPost.PluginDocumentSettingPanel;
    var el = wp.element.createElement;
    var Button = wp.components.Button;
    var withSelect = wp.data.withSelect;
    var withDispatch = wp.data.withDispatch;
    var compose = wp.compose.compose;

    var PortifolioOrderPanel = function( props ) {
        var meta = props.meta;
        var order = meta._portifolio_sections_order || [];
        var defaultSections = window.PortifolioData.sections;
        var defaultKeys = Object.keys(defaultSections);

        // Se a ordem estiver vazia ou incompleta, garantir que todas as chaves estejam presentes
        // e respeitar a ordem salva.
        // Nota: A lógica aqui é executada a cada render, mas como order vem do store, é seguro.
        // Idealmente, a inicialização seria feita no seletor ou no momento do load, mas aqui funciona.
        
        var currentOrder = [];
        if (!order || order.length === 0) {
            currentOrder = defaultKeys;
        } else {
             // Garantir que é um array (pode vir serializado ou algo assim se a API não estiver tipada corretamente, mas register_meta resolve)
             if (!Array.isArray(order)) {
                 // Tentar converter se for string (caso venha serializado, mas register_meta com array schema deve entregar array)
                 // Se falhar, reseta
                 currentOrder = defaultKeys;
             } else {
                 currentOrder = order.slice();
                 
                 // Adicionar chaves faltantes
                 var missingKeys = defaultKeys.filter(function(key) {
                     return currentOrder.indexOf(key) === -1;
                 });
                 if (missingKeys.length > 0) {
                     currentOrder = currentOrder.concat(missingKeys);
                 }
                 
                 // Remover chaves inválidas
                 currentOrder = currentOrder.filter(function(key) {
                     return defaultKeys.indexOf(key) !== -1;
                 });
             }
        }

        // Forçar "banner" como primeiro item
        if ( currentOrder.indexOf( 'banner' ) !== -1 ) {
            currentOrder = currentOrder.filter( function( key ) { return key !== 'banner'; } );
            currentOrder.unshift( 'banner' );
        }
        
        // Se a ordem calculada for diferente da salva (por adição/remoção de chaves), atualiza o store silenciosamente?
        // Melhor não atualizar automaticamente sem ação do usuário para evitar dirty state indesejado,
        // mas visualmente precisamos mostrar a lista completa.
        // Vamos usar currentOrder para renderizar.

        function updateOrder( newOrder ) {
            props.setMeta( { _portifolio_sections_order: newOrder } );
        }

        function moveUp( index ) {
            if ( index === 0 ) return;
            var newOrder = currentOrder.slice();
            var temp = newOrder[index];
            newOrder[index] = newOrder[index - 1];
            newOrder[index - 1] = temp;
            updateOrder( newOrder );
        }

        function moveDown( index ) {
            if ( index === currentOrder.length - 1 ) return;
            var newOrder = currentOrder.slice();
            var temp = newOrder[index];
            newOrder[index] = newOrder[index + 1];
            newOrder[index + 1] = temp;
            updateOrder( newOrder );
        }

        // Renderizar itens
        var listItems = currentOrder.map( function( key, index ) {
            var label = defaultSections[key];
            
            // Bloqueio do Banner
            var isBanner = key === 'banner';
            // Banner (index 0) não move. Item 1 não sobe (para não trocar com banner).
            var disableUp = index === 0 || index === 1;
            // Banner não desce. Último não desce.
            var disableDown = index === currentOrder.length - 1 || isBanner;

            return el( 'div', { 
                key: key,
                style: { 
                    display: 'flex', 
                    alignItems: 'center', 
                    justifyContent: 'space-between',
                    padding: '10px',
                    borderBottom: '1px solid #eee',
                    background: isBanner ? '#f9f9f9' : '#fff'
                }
            },
                el( 'span', { style: { fontWeight: 500, fontSize: '13px', color: isBanner ? '#888' : 'inherit' } }, 
                    label + (isBanner ? ' (Fixo)' : '') 
                ),
                el( 'div', { style: { display: 'flex', gap: '4px' } },
                    el( Button, { 
                        icon: 'arrow-up-alt2', 
                        label: 'Mover para cima',
                        isSmall: true,
                        disabled: disableUp,
                        variant: 'secondary',
                        onClick: function() { moveUp( index ); }
                    } ),
                    el( Button, { 
                        icon: 'arrow-down-alt2', 
                        label: 'Mover para baixo',
                        isSmall: true,
                        disabled: disableDown,
                        variant: 'secondary',
                        onClick: function() { moveDown( index ); }
                    } )
                )
            );
        } );

        return el( PluginDocumentSettingPanel, {
            name: 'portifolio-order-panel',
            title: 'Ordem das Seções',
            className: 'portifolio-order-panel',
        },
            el( 'div', { className: 'portifolio-order-list', style: { border: '1px solid #e0e0e0', borderRadius: '4px' } }, listItems ),
            el( 'p', { style: { color: '#666', fontSize: '12px', marginTop: '10px', fontStyle: 'italic' } }, 
                'Use as setas para reordenar as seções.' 
            )
        );
    }

    var ComposedPortifolioOrderPanel = compose(
        withSelect( function( select ) {
            return {
                meta: select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {},
            };
        } ),
        withDispatch( function( dispatch ) {
            return {
                setMeta: function( newMeta ) {
                    dispatch( 'core/editor' ).editPost( { meta: newMeta } );
                },
            };
        } )
    )( PortifolioOrderPanel );

    registerPlugin( 'portifolio-order-sidebar', {
        render: ComposedPortifolioOrderPanel,
        icon: 'menu',
    } );

} )( window.wp );
