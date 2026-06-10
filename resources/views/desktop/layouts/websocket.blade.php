<script async>
    ( async () => {
        const fetchToken = async () => {
            const tokenResp = await network.post(`${window.location.protocol}//${window.location.hostname}/authWs/token`);
            return await tokenResp.text();
        };

        const ws = new WebSocketClient();

        ws.setTokenProvider(fetchToken);
        ws.connect();

        ws.onMessage( msg => {
            console.log(msg);
            let event = new CustomEvent('userEvent', {detail: {
                    name: msg.event,
                    payload: msg.payload,
                }});
            document.dispatchEvent(event);
        });
        ws.onCloseCallback = () => console.log('WebSocket closed');
    }) ();
</script>
