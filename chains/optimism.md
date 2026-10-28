# Optimism Security Analysis

**Chain Type**: L2 Optimistic Rollup (OP Mainnet), EVM-compatible  
**Native Token**: ETH (gas), OP (governance)  
**Consensus**: Ethereum L1 + Single Sequencer (decentralizing via fault proofs)  
**Smart Contract Language**: Solidity, Vyper (OP Stack precompiles)  
**Total Value Locked (DeFi)**: ~$2B+ (2024)  
**Total Exploits (DeFiLlama)**: 15+  
**Total Value Lost**: $120M+

---

## Major Exploits (2023-2025)

### 2024-2025

| Date | Protocol | Loss | Vulnerability | Root Cause |
|------|----------|------|---------------|------------|
| 2024 | **Sonne Finance** | $20M | Donation Attack | Compound-fork donation attack |
| 2024 | **Cozy Finance V2** | $163K | Protocol Logic | Unknown logic flaw |
| 2024 | **Kokomo Finance** | $4M | Rugpull / Admin Drain | Admin drain (exit scam) |
| 2023 | **Hundred Finance** | $7.4M | Donation Attack | Compound-fork donation attack |
| 2023 | **Aerodrome V1** | $700K | Frontend / DNS Hijack | DNS hijack (also Ethereum) |

---

## Vulnerability Breakdown (Optimism)

| Classification | Count | Total Lost | Notable Examples |
|----------------|-------|------------|------------------|
| Donation Attack (Token Accounting) | ~3 | $27.4M+ | Sonne, Hundred, Tectonic (Cronos but similar) |
| Rugpull / Admin Drain | ~2 | $4M+ | Kokomo, various tokens |
| Frontend / DNS Hijack | ~2 | $700K+ | Aerodrome (cross-chain) |
| Protocol Logic | ~3 | $5M+ | Cozy, various |
| Access Control | ~2 | $1M+ | Various |
| Oracle Manipulation | ~1 | $100K+ | Minor incidents |

---

## Optimism-Specific Risk Factors

### 1. OP Stack / Superchain Architecture
- **Shared Codebase**: OP Mainnet, Base, Mode, Fraxchain, Zora, etc. share OP Stack
- **Risk**: Vulnerability in OP Stack affects all chains
- **Benefit**: Shared security upgrades, standardized bridge

### 2. Fault Proof System (Cannon)
- **Status**: Transitioning from single sequencer to fault proofs (2024+)
- **Risk**: New dispute game logic = new attack surface
- **Cannon**: MIPS-based fault proof VM

### 3. Compound Fork Concentration
- **Pattern**: Many lending protocols are Compound forks (Sonne, Hundred, etc.)
- **Risk**: Donation attack vulnerability well-known but still deployed

### 4. Single Sequencer (Currently)
- **Risk**: Sequencer censorship, MEV extraction, single point of failure
- **Mitigation**: Fault proofs enable decentralized sequencing

---

## Attack Vector Analysis

### 1. Donation Attacks on Compound Forks (Dominant on Optimism)
**Why Optimism?**: High concentration of Compound v2 forks (Sonne, Hundred)

**Mechanism**:
```
1. Attacker transfers underlying token directly to vToken contract
2. This increases `totalCash` without increasing `totalBorrows` or `totalSupply`
3. Exchange rate = (totalCash + totalBorrows - totalReserves) / totalSupply
4. Exchange rate inflates → attacker's existing shares worth more
5. Attacker redeems shares for more underlying than deposited
```

**Optimism Incidents**:
- **Sonne Finance (2024)**: $20M - Compound v2 fork, unaudited BOO market addition
- **Hundred Finance (2023)**: $7.4M - Compound fork, donation attack
- **Tectonic (Cronos, 2024)**: $124M - Same pattern, shows scale

**Mitigation**:
- **Supply caps** on new markets
- **Exchange rate safeguards**: Max exchange rate change per block
- **Donation detection**: Monitor direct transfers to vToken
- **Use Compound v3 (Comet)** - Not vulnerable to donation attacks
- **Audit before adding markets**

### 2. Rugpull / Admin Drain
**Why Optimism?**: Low deployment cost, easy token creation, anonymous teams

**Examples**:
- **Kokomo Finance (2024)**: $4M - Admin key used to drain, classic rugpull
- **Multiple token rugpulls**: Low liquidity tokens, admin mint/drain

**Mitigation**:
- Renounce ownership / timelock
- Liquidity locks (Team Finance, Unicrypt)
- Multisig for admin functions
- Transparent team / KYC

### 3. Frontend / DNS Hijacking
**Cross-Chain**: Aerodrome hit on Optimism + Ethereum

**Mechanism**: DNS hijack → malicious frontend → users sign malicious txs

**Mitigation**:
- ENS/IPFS frontend hosting
- Subresource Integrity (SRI)
- User education: verify URL, use bookmarks
- Monitoring (Forta, custom)

---

## Ease of Exploitation Assessment (Optimism)

| Vulnerability | Technical Difficulty | Capital Required | Optimism-Specific Factors |
|---------------|---------------------|------------------|-------------------------|
| Donation Attack | Low-Medium | Low | **Easier**: Known pattern, many unaudited forks |
| Rugpull / Admin Drain | Low | Low | **Easier**: Low cost, anonymous teams common |
| Frontend / DNS | Low (infra) | N/A | Same as elsewhere |
| Protocol Logic | High | Variable | Same as Ethereum |
| Oracle Manipulation | Medium | Medium | Thinner liquidity than L1 |

---

## Notable Incidents Deep Dive

### Sonne Finance (2024) - $20M
- **Protocol**: Sonne (Compound v2 fork on Optimism)
- **Vector**: Donation attack on newly added BOO market
- **Root Cause**: 
  - Added BOO market without third-party audit
  - Internal testing missed donation attack vector
  - No supply cap on new market
- **Attack**: Direct BOO transfer to vBOO → inflated exchange rate → borrow against inflated collateral
- **Admission**: Team admitted no third-party audit for the market addition
- **Lesson**: **Never deploy unaudited market additions to Compound forks**

### Hundred Finance (2023) - $7.4M
- **Protocol**: Hundred (Compound fork on Optimism)
- **Vector**: Donation attack
- **Pattern**: Same as Sonne, earlier instance
- **Lesson**: Donation attacks are **well-known** since 2021; no excuse for missing them

### Kokomo Finance (2024) - $4M
- **Protocol**: Kokomo (lending protocol)
- **Vector**: Rugpull / Admin drain
- **Root Cause**: Admin key controlled by anonymous team; used to drain
- **Lesson**: Verify team, require timelock/multisig, check liquidity locks

---

## Tooling & Detection on Optimism

### Optimism-Specific
- **Optimistic Etherscan** - Block explorer
- **OP Stack Specs** - Fault proof, bridge contracts
- **Superchain Registry** - Chain configurations

### General EVM (Full Optimism Support)
- **Foundry** - `forge test --fork-url $OPTIMISM_RPC`
- **Tenderly** - Simulation/debugging
- **Phalcon** - Transaction analysis
- **Forta** - Monitoring
- **Slither** - Static analysis (OP Stack precompiles)

---

## Recent Trends (2024-2025)

1. **Donation attacks persist** despite being known since 2021
2. **OP Stack adoption** = shared risk across Superchain
3. **Fault proof rollout** (2024+) - New audit surface
4. **Base competition** - Similar architecture, drawing TVL/exploits
5. **Aerodrome/Velodrome merger** - Concentrated DEX liquidity

---

## Recommendations for Optimism Developers

1. **Don't fork Compound v2 without donation attack fixes** - Use Comet (v3) or add supply caps + exchange rate guards
2. **Audit every market addition** - Sonne lost $20M on unaudited BOO market
3. **Use OP Stack standard bridge** - Not custom bridges
4. **Implement fault proof awareness** - Understand withdrawal finality
4. **Frontend on IPFS/ENS** - Resist DNS hijacking
5. **Multisig + timelock** - For all admin functions
6. **Monitor Superchain upgrades** - OP Stack changes affect you

---

## References

- [DefiLlama Hacks - Optimism](https://defillama.com/hacks?chain=Optimism)
- [Sonne Finance Hack - DailyCoin](https://dailycoin.com/fantoms-polter-finance-pauses-after-devastating-hack-what-we-know) (similar pattern)
- [Hundred Finance Exploit](https://defillama.com/hack/hundred-finance)
- [OP Stack Documentation](https://stack.optimism.io/)
- [Optimism Fault Proofs](https://community.optimism.io/docs/protocol/fault-proofs/)
- [Donation Attack Analysis](https://www.theblock.co/post/348785/analysis-of-700k-oracle-manipulation-exploit-highlights-vulnerabilities-in-defi-vaults)