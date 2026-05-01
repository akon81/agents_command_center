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
            window.Livewire.dispatch('agent-run-started.' + agentId, e);
        })
        .listen('.RunFinished', (e) => {
            window.Livewire.dispatch('agent-run-finished.' + agentId, e);
            window.Livewire.dispatch('panel-run-finished', { agentId: agentId, ...e });
        })
        .listen('.TaskProgressed', (e) => {
            window.Livewire.dispatch('agent-progressed.' + agentId, e);
        })
        .listen('.ActionChanged', (e) => {
            window.Livewire.dispatch('agent-action-changed.' + agentId, e);
            window.Livewire.dispatch('panel-action-changed', { agentId: agentId, ...e });
        });
};
