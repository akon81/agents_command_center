import './bootstrap';

window.initAgentCard = function (agentId, agentSlug) {
    if (!window.Echo) {
        console.warn('[AgentCard] Echo not available');
        return;
    }

    const channelName = 'agent.' + agentSlug;
    console.log('[AgentCard] subscribing to ' + channelName);

    const channel = window.Echo.channel(channelName);

    channel.subscribed(() => {
        console.log('[AgentCard] subscribed: ' + channelName);
    });

    channel.error((err) => {
        console.error('[AgentCard] subscription error on ' + channelName, err);
    });

    channel
        .listen('.RunStarted', (e) => {
            console.log('[AgentCard:' + agentSlug + '] RunStarted', e);
            window.Livewire.dispatch('agent-run-started.' + agentId, { data: e });
        })
        .listen('.RunFinished', (e) => {
            console.log('[AgentCard:' + agentSlug + '] RunFinished', e);
            window.Livewire.dispatch('agent-run-finished.' + agentId, { data: e });
        })
        .listen('.TaskProgressed', (e) => {
            console.log('[AgentCard:' + agentSlug + '] TaskProgressed', e);
            window.Livewire.dispatch('agent-progressed.' + agentId, { data: e });
        })
        .listen('.ActionChanged', (e) => {
            console.log('[AgentCard:' + agentSlug + '] ActionChanged', e);
            window.Livewire.dispatch('agent-action-changed.' + agentId, { data: e });
        });
};
