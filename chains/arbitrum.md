# Arbitrum Security Analysis

**Chain Type**: L2 Optimistic Rollup (Arbitrum One), EVM-compatible  
**Native Token**: ETH (gas), ARB (governance)  
**Consensus**: Ethereum L1 + Sequencer (decentralizing via BoLD)  
**Smart Contract Language**: Solidity, Vyper (ArbOS precompiles)  
**Total Value Locked (DeFi)**: ~$3B+ (2024)  
**Total Exploits (DeFiLlama)**: 25+  
**Total Value Lost**: $850M+

---

## Major Exploits (2023-2025)

### 2025

| Date | Protocol | Loss | Vulnerability | Root Cause |
|------|----------|------|---------------|------------|
| 2025 | **Kelp** | $293M | Bridge / Cross-Chain Message Spoofing | Cross-chain message spoofing (Ethereum + Arbitrum) |

### 2024

| Date | Protocol | Loss | Vulnerability | Root Cause |
|------|----------|------|---------------|------------|
| 2024 | **Radiant V2** | $50M | Social Engineering / Blind Signing | Multi-chain (BSC, Arbitrum) - blind signing |
| 2024 | **DeltaPrime** | $5.98M | Key Compromise | Private key compromised |
| 2024 | **FutureSwap** | $395K | Protocol Logic / Withdrawal Logic Flaw | Withdrawal logic flaw |
| 2024 | **Stake DAO Yield** | $91K | Key Compromise / Bridge | Deployer key compromised (bridge-related) |
| 2024 | **Cascade Liquidity Strategy** | $1.34M | Oracle Manipulation | Spot price manipulation |
| 2024 | **Neutra Finance** | $36.45K | Oracle Manipulation | Spot price manipulation |
| 2024 | **SquidMulticall** | $517K | Access Control | Improper access control (multi-chain) |

### 2023

| Date | Protocol | Loss | Vulnerability | Root Cause |
|------|----------|------|---------------|------------|
| 2023 | **Treasure** | $1.4M | Input Validation | Missing input validation |
| 2023 | **GMX (Arbitrum deployment)** | Part of $42M | Reentrancy | Cross-contract reentrancy (also Ethereum) |
| 2023 | **PlutusDAO** | Various | Governance / Logic | Governance attack vectors |

---

## Vulnerability Breakdown (Arbitrum)

| Classification | Count | Total Lost | Notable Examples |
|----------------|-------|------------|------------------|
| Bridge & Cross-Chain | ~3 | $293M+ | Kelp (cross-chain message spoofing) |
| Access Control | ~6 | $60M+ | Radiant (blind signing), DeltaPrime, SquidMulticall |
| Key Compromise | ~4 | $6M+ | DeltaPrime, Stake DAO |
| Protocol Logic | ~4 | $5M+ | FutureSwap, Treasure, PlutusDAO |
| Oracle Manipulation | ~3 | $1.4M+ | Cascade, Neutra |
| Social Engineering | ~2 | $50M+ | Radiant (blind signing) |
| Reentrancy | ~2 | $42M+ | GMX (cross-contract) |

---

## Arbitrum-Specific Risk Factors

### 1. Optimistic Rollup Architecture
- **Trust Assumption**: 7-day challenge period (historically), moving to BoLD (Bounded Liquidity Delay)
- **Sequencer**: Currently centralized (Offchain Labs), decentralizing
- **Risk**: Sequencer censorship, delayed finality, bridge withdrawal delays

### 2. Cross-Chain Message Passing (ArbSys, Inbox/Outbox)
- **ArbSys.precompile**: Access to L1 block hash, gas price, etc.
- **L1→L2 messages**: Delayed inbox, forced inclusion
- **L2→L1 messages**: 7-day withdrawal (challenge period)
- **Risk**: Message spoofing, replay, timing attacks (Kelp exploit)

### 3. Nitro/Stylus Upgrade
- **Nitro**: WASM-based execution (since Aug 2022)
- **Stylus**: Rust/C++ support (2024+)
- **Risk**: New VM = new bug classes; precompile interfaces

### 4. High TVL Concentration in Few Protocols
- **GMX, Radiant, Camelot, Pendle, Gains Network** dominate
- **Risk**: Single protocol exploit = large % of chain TVL

---

## Attack Vector Analysis

### 1. Cross-Chain Message Spoofing (Kelp - $293M)
**Root Cause**: Flaw in cross-chain message verification between Ethereum and Arbitrum

**Technical Details**:
- Kelp used custom cross-chain messaging
- Attacker spoofed L1→L2 message proof
- Withdrew $293M across both chains
- **Classification**: Bridge & Cross-Chain / Cross-Chain Message Spoofing

**Mitigation**:
- Use canonical Arbitrum bridge (Inbox/Outbox)
- Verify message hashes on both sides
- Use proven libraries (Arbitrum SDK, Hop, Connext)

### 2. Blind Signing / Social Engineering (Radiant - $50M)
**Root Cause**: Attacker tricked signer into signing malicious transaction

**Technical Details**:
- Multi-chain (BSC + Arbitrum)
- Signer approved malicious calldata without verification
- **Classification**: Social Engineering / Blind Signing

**Mitigation**:
- Transaction simulation (Tenderly, Phalcon)
- Clear signing (Ledger Clear Signing, Rabby)
- Multi-sig with independent verification
- Time-delayed execution for large transfers

### 3. Access Control on Cross-Chain Contracts
**Pattern**: Contracts managing cross-chain state often have complex access control

**Examples**:
- **SquidMulticall** ($517K): Improper access control across 5 chains
- **Radiant**: Cross-chain lending markets with shared admin

**Mitigation**:
- Role-based access per chain
- Timelock on cross-chain config changes
- Emergency pause per chain

### 4. Oracle Manipulation on L2
**Why Arbitrum?**: Deep Camelot/Uniswap V3 liquidity, but thinner than Ethereum mainnet

**Patterns**:
- **Cascade ($1.34M)**: Spot price manipulation on Camelot
- **Neutra ($36K)**: Similar spot manipulation

**Mitigation**:
- Chainlink on Arbitrum (native feeds)
- TWAP with L1-anchored timestamps
- Camelot/Uniswap V3 TWAP oracles

---

## Ease of Exploitation Assessment (Arbitrum)

| Vulnerability | Technical Difficulty | Capital Required | Arbitrum-Specific Factors |
|---------------|---------------------|------------------|-------------------------|
| Cross-Chain Message Spoofing | High | High | **Unique**: Custom bridges vulnerable; canonical bridge safer |
| Blind Signing / Social Eng. | Low (human) | N/A | **High risk**: Large treasuries, multi-sig signers targeted |
| Access Control | Low | Low | **Easier**: Complex cross-chain permissions |
| Oracle Manipulation | Medium | Medium | **Easier**: Thinner liquidity than L1 |
| Key Compromise | N/A (OpSec) | N/A | Same as elsewhere |
| Protocol Logic | High | Variable | Same as Ethereum |

---

## Notable Incidents Deep Dive

### Kelp Exploit (2024) - $293M
- **Protocol**: Kelp (liquid restaking, rsETH)
- **Chains**: Ethereum + Arbitrum
- **Vector**: Cross-chain message spoofing
- **Root Cause**: Custom bridge message verification flaw
- **Impact**: 98% of Arbitrum's stolen value from single incident
- **Lesson**: Use canonical bridges; audit custom cross-chain logic extensively

### Radiant V2 (2024) - $50M
- **Protocol**: Radiant Capital (cross-chain lending)
- **Chains**: BSC + Arbitrum
- **Vector**: Blind signing / social engineering
- **Root Cause**: Signer approved malicious `multicall` data
- **Impact**: Cross-chain lending markets drained
- **Lesson**: Clear signing, transaction simulation, independent verification

### GMX V1 (2024/2025) - $42M
- **Protocol**: GMX (perp DEX)
- **Chains**: Arbitrum (primary) + Ethereum
- **Vector**: Cross-contract reentrancy in `executeDecreaseOrder`
- **Root Cause**: Unaudited 2022 patch; refund process allowed re-entry
- **Impact**: GLP valuation manipulation, AUM drain
- **Lesson**: Reentrancy guards on ALL external calls; audit patches

---

## Tooling & Detection on Arbitrum

### Arbitrum-Specific
- **Arbiscan** - Block explorer
- **Arbitrum Nova Explorer** - For Nova chain
- **ArbOS Precompile Docs** - `ArbSys`, `ArbOwner`, etc.
- **Nitro/Stylus Debugging** - WASM/Rust tooling

### General EVM (Full Arbitrum Support)
- **Foundry** - `forge test --fork-url $ARBITRUM_RPC`
- **Tenderly** - Simulation/debugging (Arbitrum support)
- **Phalcon** - Transaction analysis (Arbitrum)
- **Forta** - Monitoring (Arbitrum network)
- **Slither** - Static analysis (ArbOS precompiles need special handling)

---

## Recent Trends (2024-2025)

1. **Cross-chain exploits dominate value** (Kelp = 98% of Arbitrum losses)
2. **Social engineering targeting multi-sigs** (Radiant, increasing trend)
3. **Canonical bridge adoption increasing** - Custom bridges declining
4. **Stylus (Rust) contracts emerging** - New audit surface
5. **BoLD upgrade** - Decentralized proving, shorter withdrawal delays
6. **GMX V2 migration** - V1 deprecated after exploit

---

## Recommendations for Arbitrum Developers

1. **Use canonical Arbitrum bridge** - Don't build custom message passing
2. **Implement clear signing** - Ledger Clear Signing, Rabby, simulation
3. **Per-chain access control** - Separate roles per chain deployment
4. **Chainlink oracles on Arbitrum** - Native feeds available
5. **Audit Nitro/Stylus specifics** - Precompiles, gas metering, WASM
6. **Monitor L1→L2 message delays** - Forced inclusion timing
7. **Test on Arbitrum Sepolia** - Before mainnet deployment
8. **Emergency pause per chain** - Not global only

---

## References

- [DefiLlama Hacks - Arbitrum](https://defillama.com/hacks?chain=Arbitrum)
- [Kelp Hack Analysis](https://www.halborn.com/blog/post/explained-the-kelp-hack)
- [Radiant Capital Exploit](https://defillama.com/hack/radiant-v2)
- [Arbitrum Nitro Documentation](https://developer.arbitrum.io/nitro)
- [Arbitrum Bridge Security](https://developer.arbitrum.io/bridging)
- [GMX V1 Exploit Analysis](https://www.halborn.com/blog/post/explained-the-gmx-hack-july-2025)
- [BoLD Protocol](https://github.com/OffchainLabs/bold)