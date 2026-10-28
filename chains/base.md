# Base Security Analysis

**Chain Type**: L2 Optimistic Rollup (OP Stack), EVM-compatible  
**Native Token**: ETH (gas)  
**Consensus**: Ethereum L1 + Single Sequencer (Coinbase, decentralizing via OP Stack fault proofs)  
**Smart Contract Language**: Solidity, Vyper  
**Total Value Locked (DeFi)**: ~$2B+ (2024)  
**Total Exploits (DeFiLlama)**: 8+  
**Total Value Lost**: $40M+

---

## Major Exploits (2023-2025)

### 2025

| Date | Protocol | Loss | Vulnerability | Root Cause |
|------|----------|------|---------------|------------|
| Nov 2025 | **Aerodrome Finance** | TBD (frontend) | Frontend / DNS Hijack | DNS hijacking on centralized domains; smart contracts unaffected |
| 2025 | **Allbridge** | $191K | Bridge / Bridge Logic Flaw | Bridge logic flaw |
| 2025 | **Impermax V3** | $300K | Token Accounting / Incorrect Fee Accounting | Fee accounting error |
| 2025 | **Kipseli** | $72K | Input Validation | Missing input validation |
| 2025 | **GoldPesa** | $115K | Token Accounting / Incorrect Share Accounting | Share accounting error |
| 2025 | **FOOM Cash** | $2.26M | Bridge / Forged Proof | Cross-chain forged proof (Ethereum + Base) |
| 2025 | **SquidMulticall** | $517K | Access Control | Improper access control (5 chains) |

### 2024

| Date | Protocol | Loss | Vulnerability | Root Cause |
|------|----------|------|---------------|------------|
| 2024 | **Matcha** | $13.43M | Frontend / Frontend Compromise | Frontend compromise (Base + Ethereum) |
| 2024 | **Aerodrome V1** | $700K | Frontend / DNS Hijack | DNS hijack (Ethereum + Optimism + Base) |
| 2024 | **Panoptic V1.1** | $0 | Input Validation / Hash Collision | Hash collision found (no loss) |

---

## Vulnerability Breakdown (Base)

| Classification | Count | Total Lost | Notable Examples |
|----------------|-------|------------|------------------|
| Frontend & Infrastructure | ~4 | $14M+ | Matcha, Aerodrome (DNS), Panoptic |
| Bridge & Cross-Chain | ~3 | $2.5M+ | Allbridge, FOOM Cash, SquidMulticall |
| Access Control | ~2 | $500K+ | SquidMulticall, various |
| Token & Share Accounting | ~2 | $400K+ | Impermax, GoldPesa |
| Input Validation | ~2 | $100K+ | Kipseli, Panoptic |
| Protocol Logic | ~1 | $0 | Panoptic (hash collision, no loss) |

---

## Base-Specific Risk Factors

### 1. OP Stack / Superchain Member
- **Shared Codebase**: Inherits OP Stack upgrades/risks from Optimism
- **Bridge**: Standard OP Stack bridge to Ethereum (L2OutputOracle, L1CrossDomainMessenger)
- **Risk**: OP Stack vulnerability = Base vulnerability

### 2. Coinbase Sequencer (Currently)
- **Centralized Sequencer**: Coinbase operates sole sequencer
- **Risk**: Censorship, MEV, single point of failure
- **Revenue**: Sequencer fees → Coinbase (partial to Base ecosystem fund)
- **Decentralization**: Fault proofs roadmap (shared with Optimism)

### 3. Rapid Growth = Rapid Deployment
- **Ecosystem Incentives**: Base Camp, grants, Onchain Summer
- **Risk**: Speed over security; unaudited contracts for incentives
- **Example**: Aerodrome (major DEX) hit by frontend attacks repeatedly

### 4. Aerodrome/Velodrome Concentration
- **Dominant DEX**: Aerodrome = primary liquidity hub
- **Risk**: Single protocol failure = chain-wide impact
- **Frontend Target**: High-value target for DNS/phishing

---

## Attack Vector Analysis

### 1. Frontend / DNS Attacks (Dominant on Base)
**Why Base?**: Aerodrome is high-value target; Coinbase brand attracts phishing

**Incidents**:
- **Aerodrome (Nov 2025)**: DNS hijack on `.finance` and `.box` domains; users redirected to phishing site requesting malicious signatures
- **Aerodrome (Nov 2023)**: Two DNS hijacks, ~$300K user losses
- **Matcha (2024)**: Frontend compromise on Base + Ethereum, $13.43M
- **Velodrome (Nov 2023)**: Frontend compromise (Optimism, but same team)

**Pattern**: Attackers target DNS providers (My.box, etc.) or CDN
**Impact**: Users sign `permit`, `setApprovalForAll`, or malicious calldata
**Smart Contracts**: Unaffected in all cases

**Mitigation**:
- **Decentralized frontend**: ENS + IPFS (Aerodrome uses `aero.drome.eth.limo`, `aero.drome.eth.link`)
- **SRI hashes** for all CDN resources
- **DNSSEC** on domains
- **Monitor** certificate transparency logs
- **User education**: Bookmark official URLs, verify before signing

### 2. Bridge Logic Flaws
**Why Base?**: High bridge volume (Coinbase on-ramp, OP Stack bridge)

**Incidents**:
- **Allbridge (2025)**: $191K - Bridge logic flaw
- **FOOM Cash (2025)**: $2.26M - Forged proof (Ethereum + Base)
- **SquidMulticall (2025)**: $517K - Improper access control (5 chains including Base)

**Mitigation**:
- Use canonical OP Stack bridge (L2OutputOracle)
- Audit custom bridge logic extensively
- Monitor L1→L2 message relay

### 3. Token Accounting Errors
**Incidents**:
- **Impermax V3 (2025)**: $300K - Incorrect fee accounting
- **GoldPesa (2025)**: $115K - Incorrect share accounting

**Pattern**: Math errors in fee/share calculation, often in newer/less-audited protocols

**Mitigation**:
- Formal verification for accounting logic
- Invariant testing (totalSupply == sum balances, etc.)
- Multiple audits for accounting-heavy protocols

---

## Ease of Exploitation Assessment (Base)

| Vulnerability | Technical Difficulty | Capital Required | Base-Specific Factors |
|---------------|---------------------|------------------|-------------------------|
| Frontend / DNS | Low (infra) | N/A | **High risk**: Aerodrome is high-profile target |
| Bridge Logic | High | High | OP Stack bridge is solid; custom bridges risky |
| Token Accounting | Medium | Low | New protocols, less audit coverage |
| Access Control | Low | Low | Same as elsewhere |
| Input Validation | Medium | Low | Same as elsewhere |

---

## Notable Incidents Deep Dive

### Aerodrome DNS Hijacks (2023, 2025)
- **Protocol**: Aerodrome (Base's largest DEX, ~$60M+ TVL)
- **Vector**: DNS hijacking on centralized domains
- **2023**: Two incidents, ~$300K user losses
- **2025**: DNS hijack on `.finance` and `.box` domains via My.box provider
- **Response**: Team directed users to ENS/IPFS mirrors (`aero.drome.eth.limo`, `aero.drome.eth.link`)
- **Smart Contracts**: Never compromised
- **Lesson**: **Frontend infrastructure is the weak link for major DEXes**

### Matcha Frontend Compromise (2024) - $13.43M
- **Protocol**: Matcha (0x DEX aggregator)
- **Chains**: Base + Ethereum
- **Vector**: Frontend compromise (not DNS - actual frontend code modified)
- **Impact**: Users swapped via malicious frontend → funds drained
- **Lesson**: Frontend supply chain security critical; monitor build/deploy pipeline

### Allbridge (2025) - $191K
- **Protocol**: Allbridge (cross-chain bridge)
- **Vector**: Bridge logic flaw
- **Root Cause**: Flaw in message verification / token minting logic
- **Lesson**: Bridge code is high-value, high-complexity; needs formal verification

---

## Tooling & Detection on Base

### Base-Specific
- **BaseScan** - Block explorer (Blockscout)
- **Base Bridge UI** - Official bridge monitoring
- **Coinbase Developer Platform** - APIs, RPC

### General EVM (Full Base Support)
- **Foundry** - `forge test --fork-url $BASE_RPC`
- **Tenderly** - Simulation/debugging
- **Phalcon** - Transaction analysis
- **Forta** - Monitoring
- **Slither** - Static analysis

---

## Recent Trends (2024-2025)

1. **Frontend attacks > smart contract bugs** on Base
2. **Aerodrome repeatedly targeted** - High value, centralized domains
3. **Bridge exploits** as cross-chain volume grows
4. **Token accounting errors** in newer protocols (Impermax, GoldPesa)
5. **OP Stack shared risk** - Optimism faults affect Base
6. **Panoptic hash collision** - Found via testing, no loss (good example of detection)

---

## Recommendations for Base Developers

1. **Host frontend on ENS/IPFS** - Not centralized DNS (Aerodrome learned this)
2. **Use canonical OP Stack bridge** - Don't build custom bridges
3. **Invariant testing for accounting** - Foundry/Echidna for math-heavy contracts
4. **SRI + DNSSEC + CT monitoring** - If using centralized domains
5. **Audit before incentives** - Don't deploy unaudited for Base Camp/grants
6. **Monitor OP Stack upgrades** - Shared codebase = shared risk
7. **Clear signing support** - For user-facing protocols

---

## References

- [DefiLlama Hacks - Base](https://defillama.com/hacks?chain=Base)
- [Aerodrome DNS Hijack Nov 2025](https://www.coindesk.com/web3/2025/11/22/aerodrome-finance-hit-by-front-end-attack-users-urged-to-avoid-main-domain)
- [Aerodrome DNS Hijack Nov 2023](https://cointelegraph.com/news/aerodrome-velodrome-de-fi-platforms-frontend-hacks)
- [Matcha Frontend Compromise](https://defillama.com/hack/matcha)
- [Allbridge Exploit](https://defillama.com/hack/allbridge)
- [OP Stack Documentation](https://stack.optimism.io/docs)
- [Base Bridge Architecture](https://docs.base.org/bridging)