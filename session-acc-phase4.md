# Session Summary — Agent Command Center (ACC) Phase 1–4

**Data:** 2026-04-30  
**Projekt:** `C:\Herd\agents_command_center`  
**GitHub:** repozytorium utworzone i wypushowane przez użytkownika

---

## Cel projektu

Desktop app — real-time dashboard do zarządzania 11 Claude Code subagentami.  
Stack: **Laravel 12 + Livewire 4 + Laravel Reverb + NativePHP 1.3.0 + SQLite**

---

## Co zostało zrobione

### Ekosystem subagentów (`C:\Herd\claude\automatyzacja`)

- Zweryfikowano i poprawiono `agent-forger.md` (skrócono description do 117 znaków, przywrócono `(current)` w pipeline sekcji)
- Dodano `TaskUpdate` do whitelisty w `CLAUDE.md`
- Zbudowano **10 nowych subagentów pipeline'u** w `.claude/agents/`:
  - Planning/opus: `planner.md`, `blueprint.md`, `roadmap.md`
  - Execution/sonnet: `coder.md`, `designer.md`
  - Review: `code-review.md`, `qa.md`, `design-qa.md`
  - Discovery: `code-explainer.md`
  - Utility/haiku: `scribe-worker.md`
- Wszystkie 10 agentów wyposażono w sekcję `## Progress Events` emitującą JSON: `{"type":"progress","step":N,"total":M,"label":"..."}`

### ACC App — Phase 1: Fundament

- Laravel 12 (czysty reinstall z `laravel/laravel:^12.0` ze względu na NativePHP)
- SQLite + WAL mode
- Migracje: `agents`, `runs`, `run_logs`
- Seed: 11 agentów z layer/color/icon
- `.env`: SESSION_DRIVER=file, CACHE_STORE=file, BROADCAST_CONNECTION=reverb
- Reverb skonfigurowany na 127.0.0.1:8080

### Phase 2: Livewire Dashboard

- `AgentGrid` — ładuje wszystkich 11 agentów, sortuje wg priorytetów warstw
- `AgentCard` — per-agent card z real-time statusem, progress bar, current_action
- Layout: Linear/Vercel-inspired dark SaaS (#0a0a0b bg, #5e6ad2 accent)
- Echo + Pusher JS łączy się z Reverb (public channels, nie private)

### Phase 3: Queue + Events

- `LaunchAgentJob` — spawnuje process, broadcastuje RunStarted/RunFinished
- `LogIngester` — bulk insert logów, broadcastuje TaskProgressed/ActionChanged/LogAppended
- `ProgressParser` — chain of strategies do parsowania JSON progress eventów
- 5 Events: RunStarted, RunFinished, TaskProgressed, ActionChanged, LogAppended
  - Wszystkie używają public `Channel` (nie PrivateChannel)
  - Wszystkie mają `broadcastAs(): string` z prostą nazwą
  - JS słucha z wiodącą kropką: `.listen('.RunStarted', ...)`
- Debugowanie WebSocket (dodano raw listener w bootstrap.js — **do usunięcia**)
- Naprawiono: stale cache queue workera → restart `php artisan queue:work`
- Działa: live updates bez odświeżania strony

### Phase 4: Real Claude CLI Integration (częściowo — agent uderzył w rate limit)

Pliki **które agent miał stworzyć** (weryfikacja wymagana!):

| Plik | Opis |
|------|------|
| `app/Services/ClaudeCliCommand.php` | Buduje prawdziwe polecenie claude CLI |
| `app/Services/Progress/StreamJsonStrategy.php` | Parsuje stream-json events claude |
| `app/Services/Progress/ActionSignal.php` | DTO dla tool_use action |
| `app/Services/Progress/ToolUseExtractor.php` | Ekstrahuje tool_use ze stream-json |

Komenda claude CLI (potwierdzony format):
```
claude -p --agent <slug> --output-format stream-json --verbose \
  --include-partial-messages --dangerously-skip-permissions \
  --add-dir <workspace> "<prompt>"
```
CWD: `C:\Herd\claude\automatyzacja`

`TestAgentRun.php` — dodano `--workspace` option (default: `C:\Herd`):
```bash
php artisan agent:test coder --prompt="Say hello in one short sentence." --workspace="C:\Herd"
```

---

## Naprawione błędy (dla kontekstu)

| Problem | Rozwiązanie |
|---------|-------------|
| `sessions` table missing | SESSION_DRIVER=file |
| `cache` table missing | CACHE_STORE=file |
| `Unable to locate component [layouts.app]` | Zmiana na `@extends('layouts.app')` |
| NativePHP incompatible with Laravel 13 | Reinstall z Laravel 12 |
| `initAgentCard is not defined` | Usunięcie osobnego importu Alpine (Livewire 4 ma własny) |
| 403 na `/broadcasting/auth` | Zmiana z PrivateChannel na public Channel |
| Live updates nie działały | Restart queue workera (stale config) |
| AgentCard showing 'idle' po refresh | `mount()` ładuje latest run z DB |

---

## Pliki kluczowe

```
C:\Herd\agents_command_center\
├── app/
│   ├── Events/
│   │   ├── ActionChanged.php         # broadcast: agent.{slug}
│   │   ├── LogAppended.php           # broadcast: runs.{runId}
│   │   ├── RunFinished.php           # broadcast: agent.{slug} + dashboard
│   │   ├── RunStarted.php            # broadcast: agent.{slug} + dashboard
│   │   └── TaskProgressed.php        # broadcast: agent.{slug}
│   ├── Jobs/
│   │   └── LaunchAgentJob.php        # spawnuje process, broadcastuje
│   ├── Livewire/
│   │   ├── AgentCard.php             # per-agent live card
│   │   └── AgentGrid.php            # grid 11 agentów
│   ├── Services/
│   │   ├── ClaudeCliCommand.php      # [Phase 4 — weryfikuj!]
│   │   ├── Logging/LogIngester.php   # bulk insert + broadcasting
│   │   └── Progress/
│   │       ├── ActionSignal.php      # [Phase 4 — weryfikuj!]
│   │       ├── ProgressParser.php
│   │       ├── StreamJsonStrategy.php # [Phase 4 — weryfikuj!]
│   │       └── ToolUseExtractor.php  # [Phase 4 — weryfikuj!]
│   └── Console/Commands/
│       └── TestAgentRun.php         # php artisan agent:test <slug>
├── resources/js/
│   ├── app.js                       # initAgentCard(), bez Alpine import
│   └── bootstrap.js                 # Echo + Reverb + DEBUG listener (usunąć!)
└── .env                             # REVERB_*, SESSION_DRIVER=file
```

---

## Pilne do zrobienia (następna sesja)

1. **Zweryfikuj Phase 4 pliki** — sprawdź czy `ClaudeCliCommand.php`, `StreamJsonStrategy.php`, `ToolUseExtractor.php` istnieją i są poprawne
2. **Test realnej integracji CLI:**
   ```bash
   php artisan agent:test coder --prompt="Say hello in one short sentence and stop."
   ```
3. **Usuń debug listener** z `resources/js/bootstrap.js` (linie 21-30)
4. **Phase 5** — DialogPanel: chat UI do interakcji z agentami, wysyłanie promptów
5. **Phase 6** — NativePHP polish: tray icon, OS notifications, `.exe` build
6. **Phase 7** — Hardening: cleanup orphaned runs, error boundaries

---

## Uruchamianie (Herd)

```bash
# Terminal 1 — Reverb WebSocket server
php artisan reverb:start --debug

# Terminal 2 — Queue worker
php artisan queue:work

# Terminal 3 — Vite (dev assets)
npm run dev

# Aplikacja dostępna pod:
# http://agents_command_center.test (przez Herd)
```

---

## Kontekst dla nowej sesji

Projekt jest pod `C:\Herd\agents_command_center`. Subagenci żyją w `C:\Herd\claude\automatyzacja\.claude/agents/`. Live updates działają przez Reverb WebSocket. Następnym krokiem jest weryfikacja Phase 4 (real claude CLI) i ewentualne dokończenie integracji, a potem Phase 5 (DialogPanel).
