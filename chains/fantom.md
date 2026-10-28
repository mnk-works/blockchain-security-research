# Fantom Security Analysis

**Chain Type**: L1, EVM-compatible (Opera)  
**Native Token**: FTM  
**Consensus**: Lachesis (aBFT DAG-based)  
**Smart Contract Language**: Solidity, Vyper  
**Total Value Locked (DeFi)**: ~$200M+ (2024, down from peak)  
**Total Exploits (DeFiLlama)**: 8+  
**Total Value Lost**: $165M+

---

## Major Exploits (2023-2025)

### 2024

| Date | Protocol | Loss | Vulnerability | Root Cause |
|------|----------|------|---------------|------------|
| Nov 2024 | **Polter Finance** | $8.7M | Oracle Manipulation | Flash loan BOO reserve manipulation → inflated oracle price |
| 2023 | **Multichain Bridge** | $125M | Key Compromise / Bridge | MPC key access lost after CEO arrest; inside job suspected |

### 2023

| Date | Protocol | Loss | Vulnerability | Root Cause |
|------|----------|------|---------------|------------|
| Jul 2023 | **Multichain** | ~$130M | Bridge / Key Compromise | CEO arrest → MPC key loss → unauthorized withdrawals |
| 2023 | **DEUS Finance** | $13.4M | Oracle Manipulation | Spot price manipulation on Fantom DEX |
| 2023 | **Reaper Farm** | $1.7M | Access Control | Improper access control |
| 2023 | **Fantom Foundation** | $7M | Key Compromise | Hot wallet compromised (Chrome zero-day) |

### 2021

| Date | Protocol | Loss | Vulnerability | Root Cause |
|------|----------|------|---------------|------------|
| Dec 2021 | **Grim Finance** | $16.5M+ | Reentrancy | 5x reentrancy on strategy contracts |

---

## Vulnerability Breakdown (Fantom)

| Classification | Count | Total Lost | Notable Examples |
|----------------|-------|------------|------------------|
| Bridge / Key Compromise | ~2 | $250M+ | Multichain ($125M + $130M) |
| Oracle Manipulation | ~3 | $22M+ | Polter ($8.7M), DEUS ($13.4M) |
| Reentrancy | ~1 | $16.5M+ | Grim Finance |
| Access Control | ~1 | $1.7M | Reaper Farm |
| Key Compromise (Foundation) | ~1 | $7M | Fantom Foundation hot wallet |

---

## Fantom-Specific Risk Factors

### 1. Lachesis Consensus (aBFT DAG)
- **Finality**: ~1-2 seconds (fast finality)
- **No mempool**: Transactions ordered by DAG
- **Risk**: Different MEV dynamics; front-running harder but not impossible
- **Implication**: Reentrancy timing slightly different but still exploitable

### 2. Multichain Bridge Dependency (Historical)
- **Status**: Multichain collapsed July 2023
- **Impact**: $125M+ lost from Fantom deployments; FTM depegged temporarily
- **Lesson**: Single bridge dependency = systemic risk

### 3. SpookySwap as Primary DEX
- **Concentration**: Most liquidity on SpookySwap V2/V3
- **Oracle Risk**: Protocols using SpookySwap spot price vulnerable
- **Polter Example**: BOO token price manipulated via SpookySwap pools

### 4. Admin Key Compromise Pattern
- **Fantom Foundation**: Hot wallet hacked (Chrome zero-day) → $7M loss
- **Multichain**: MPC key loss after CEO arrest
- **Pattern**: Operational security failures, not smart contract bugs

### 5. Dormant Admin Tokens
- **Incident**: Fantom Foundation wallet held dormant admin token for FTM contract on Ethereum
- **Risk**: Could have minted 1B FTM on Ethereum
- **Resolution**: Researcher found it, $1.7M bounty paid

---

## Attack Vector Analysis

### 1. Bridge / Multichain Collapse (Largest Loss - $250M+)
**Multichain (Jul 2023)**:
- **Root Cause**: CEO (Zhaojun) arrested → MPC key shares inaccessible
- **Evidence**: Pointed to inside job; keys used for unauthorized withdrawals
- **Chains Affected**: Ethereum, Fantom, BNB Chain, others
- **Fantom Impact**: ~1/3 of total losses ($40M+ directly, more indirectly)
- **Classification**: Private Key Compromise / Bridge

**Lesson**: **MPC bridges with centralized key management = single point of failure**

### 2. Oracle Manipulation via SpookySwap (Polter - $8.7M, DEUS - $13.4M)
**Polter Finance (Nov 2024) - $8.7M**:
- **Protocol**: Lending protocol (Polter)
- **Vector**: BOO token oracle manipulation
- **Mechanism**:
  1. Flash borrow BOO from SpookySwap V2 + V3 pools
  2. Deplete BOO reserves → price skyrockets (oracle calculated $1.37T per BOO)
  3. Deposit minimal BOO as collateral
  4. Borrow all other assets from lending pools
- **Root Cause**: 
  - Oracle used `ChainlinkUniV2Adapter` → `PriceFeedV2` → spot reserves
  - New BOO market added **day before** without third-party audit
  - Team admitted internal testing missed it
- **Classification**: Oracle Manipulation

**DEUS Finance (2023) - $13.4M**:
- **Vector**: Similar spot price manipulation on SpookySwap
- **Pattern**: Single DEX spot price oracle = manipulation target

### 3. Reentrancy (Grim Finance - $16.5M+)
**Grim Finance (Dec 2021)**:
- **Protocol**: Yield optimizer (Yearn fork)
- **Vector**: 5x reentrancy on strategy contracts
- **Mechanism**: Reenter `deposit()` 5x before state update
- **Classification**: Reentrancy
- **Lesson**: Forks of audited code (Yearn) can introduce reentrancy if not careful

### 4. Operational Security Failures
**Fantom Foundation (Oct 2023) - $7M**:
- **Vector**: Hot wallet compromised via Chrome zero-day
- **Discovery**: Researcher found dormant admin token in hacked wallet
- **Potential**: Could have minted 1B FTM on Ethereum
- **Resolution**: $1.7M bounty paid

**Multichain CEO Arrest**:
- **Vector**: Centralized key custody
- **Lesson**: **No single person should control bridge keys**

---

## Ease of Exploitation Assessment (Fantom)

| Vulnerability | Technical Difficulty | Capital Required | Fantom-Specific Factors |
|---------------|---------------------|------------------|-------------------------|
| Bridge/MPC Compromise | N/A (OpSec) | N/A | **Critical**: Multichain showed systemic risk |
| Oracle Manipulation | Medium | Medium | **Easier**: SpookySwap concentration, thin liquidity |
| Reentrancy | Low-Medium | Low | Same as Ethereum |
| Key Compromise | N/A (OpSec) | N/A | **High**: Foundation + Multichain both hit |
| Access Control | Low | Low | Same as elsewhere |

---

## Notable Incidents Deep Dive

### Polter Finance (Nov 2024) - $8.7M
- **Protocol**: Polter (lending on Fantom)
- **Token**: BOO (SpookySwap governance)
- **Attack**:
  1. Flash loan BOO from SpookySwap V2 + V3
  2. Drained BOO reserves → oracle price → $1.37T/BOO
  3. Deposited tiny BOO → borrowed everything
- **Root Cause**: 
  - Oracle: `AaveOracle` → `ChainlinkUniV2Adapter` → `PriceFeedV2` (spot reserves)
  - BOO market added **1 day before exploit**
  - **No third-party audit** for new market
  - Team admitted internal testing missed vulnerability
- **Aftermath**: Polter shut down

### Multichain Collapse (Jul 2023) - $125M+ (Fantom portion)
- **Protocol**: Multichain (cross-chain bridge)
- **Trigger**: CEO Zhaojun arrested by Chinese authorities
- **Mechanism**: MPC key shares held by team → inaccessible → unauthorized withdrawals
- **Fantom Impact**: ~$40M direct + FTM depeg ripple effects
- **Classification**: Private Key Compromise / Bridge
- **Aftermath**: Fantom Foundation sued Multichain; sought recovery

### Grim Finance (Dec 2021) - $16.5M+
- **Protocol**: Grim Finance (yield optimizer)
- **Vector**: 5x reentrancy on `deposit()` in strategy contracts
- **Root Cause**: Forked Yearn strategies but missed reentrancy protection
- **Classification**: Reentrancy

---

## Tooling & Detection on Fantom

### Fantom-Specific
- **FTMScan** - Block explorer
- **Fantom Foundation Security** - Bug bounty ($1.7M paid)
- **SpookySwap Analytics** - Pool monitoring

### General EVM (Full Fantom Support)
- **Foundry** - `forge test --fork-url $FANTOM_RPC`
- **Tenderly** - Simulation/debugging
- **Phalcon** - Transaction analysis
- **Forta** - Monitoring
- **Slither** - Static analysis

---

## Recent Trends (2024-2025)

1. **Multichain collapse** = end of major bridge dependency
2. **Oracle manipulation** persists (Polter 2024) - same SpookySwap pattern
3. **No third-party audits** for market additions (Polter admitted this)
4. **Foundation key compromise** shows OpSec gaps
5. **TVL decline** = fewer high-value targets but same vulnerability patterns

---

## Recommendations for Fantom Developers

1. **Never use single DEX spot price for oracles** - Use Chainlink, Pyth, or TWAP
2. **Audit EVERY market addition** - Polter lost $8.7M on unaudited BOO market
3. **Decentralize bridge key management** - No single person/MPC with centralized custody
4. **Hardware wallets for foundation/treasury keys** - Chrome zero-day = $7M lesson
5. **Reentrancy guards on all strategies** - Grim Finance was a fork that missed this
6. **Monitor SpookySwap pool depths** - For oracle manipulation detection

---

## References

- [DefiLlama Hacks - Fantom](https://defillama.com/hacks?chain=Fantom)
- [Polter Finance Hack - SmartContractHacking](https://smartcontractshacking.com/hacks/polter-finance-hack-2024)
- [Fantom Seeks Money from Multichain - CoinDesk](https://www.coindesk.com/business/2024/03/05/fantom-seeks-money-back-from-multichains-200m-exploit)
- [Fantom Foundation Exploit Analysis](https://medium.com/neptune-mutual/analysis-of-the-fantom-foundation-exploit-3990ba8f6d94)
- [Fantom Bounty Program](https://fantom.foundation/blog/security-researcher-awarded-1-7-million-by-fantom-foundation/)
- [Multichain Collapse - CoinDesk](https://www.coindesk.com/tech/2023/07/07/multichain-team-confirms-exploit-across-fantom-moonriver-and-dogechain-bridges)
- [Grim Finance Reentrancy](https://defillama.com/hack/grim-finance)