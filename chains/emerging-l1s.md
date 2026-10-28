# Emerging L1s Security Analysis

Covers: **Sui, Aptos, Tron, Near, Celo, Klaytn, Flow, Cardano, Stacks, Sui, Aptos, Sei, Monad, Berachain**

---

## Sui

**Chain Type**: L1, Move-based (Move VM)  
**Consensus**: Narwhal/Bullshark (DAG-based)  
**Smart Contract Language**: Move  
**Object Model**: Object-centric (not account-based)

### Major Exploits

| Date | Protocol | Loss | Vulnerability |
|------|----------|------|---------------|
| 2024 | **Cetus CLMM** | $223M | Token Accounting / Arithmetic Error |

### Cetus CLMM (2024) - $223M
- **Protocol**: Cetus (CLMM DEX on Sui)
- **Vector**: Arithmetic error in concentrated liquidity math
- **Root Cause**: Rounding/precision error in tick calculation
- **Classification**: Token & Share Accounting / Arithmetic Error
- **Language**: Move
- **Significance**: Largest exploit on non-EVM L1 to date

### Sui-Specific Risk Factors
1. **Move Language**: Resource-oriented, linear types (no reentrancy by design)
2. **Object Model**: Objects owned by addresses or shared; different access patterns
3. **No Reentrancy**: Move's ownership model prevents classic reentrancy
4. **New Math Libraries**: CLMM math unaudited in many deployments
5. **Package Upgrades**: Move packages upgradable; governance risk

### Tooling
- **Sui Explorer** - Block explorer
- **Move Prover** - Formal verification for Move
- **Sui CLI** - Deployment, testing

---

## Aptos

**Chain Type**: L1, Move-based (Move VM, different from Sui)  
**Consensus**: AptosBFT (v4)  
**Smart Contract Language**: Move  
**Account Model**: Account-based with resources

### Major Exploits
- **No major DeFi exploits reported yet** (as of 2024)
- **Lower TVL** = fewer targets

### Aptos-Specific Risk Factors
1. **Move Language**: Similar benefits to Sui (no reentrancy, resource safety)
2. **Block-STM**: Parallel execution; new concurrency bugs possible
3. **Module Upgrades**: Governance-controlled upgrades
4. **Lower Audit Coverage**: Fewer Move auditors than Solidity

---

## Tron

**Chain Type**: L1, EVM-compatible  
**Consensus**: DPoS (27 Super Representatives)  
**Smart Contract Language**: Solidity  
**TVL**: ~$8B+ (high, mostly USDT)

### Major Exploits

| Date | Protocol | Loss | Vulnerability |
|------|----------|------|---------------|
| 2023 | **HTX / Poloniex** | ~$100M+ | Key Compromise (HTX hot wallet) |
| 2023 | **Nobitex** | $82M | Key Compromise / Malware (multi-chain) |

### Tron-Specific Risk Factors
1. **Centralized DPoS**: 27 SRs = centralization risk
2. **USDT Dominance**: ~$50B+ USDT on Tron; bridge/DEX targets
3. **Low Gas**: Cheap attacks, spam
4. **Key Compromise Pattern**: Multiple exchange hot wallet hacks

---

## Near Protocol

**Chain Type**: L1, Nightshade sharding  
**Consensus**: Doomslug (PoS)  
**Smart Contract Language**: Rust (WASM), AssemblyScript  
**Account Model**: Account-based with access keys

### Major Exploits

| Date | Protocol | Loss | Vulnerability |
|------|----------|------|---------------|
| 2023 | **Moola Market** | $8.4M | Oracle Manipulation (Celo but Near has similar) |

### Near-Specific Risk Factors
1. **WASM Runtime**: Different vulnerability classes (memory safety, but logic bugs)
2. **Access Keys**: Fine-grained permissions; key management complexity
3. **Sharding**: Cross-shard communication complexity
4. **Aurora (EVM on Near)**: Inherits EVM risks

---

## Celo

**Chain Type**: L1, EVM-compatible, mobile-first  
**Consensus**: PoS (BFT)  
**Smart Contract Language**: Solidity  

### Major Exploits

| Date | Protocol | Loss | Vulnerability |
|------|----------|------|---------------|
| 2022 | **Moola Market** | $8.4M | Oracle Manipulation |
| 2023 | **Various** | Minor | Various |

### Moola Market (2022) - $8.4M
- **Vector**: Oracle manipulation (cUSD/CELO pool)
- **Classification**: Oracle Manipulation / Spot Price Manipulation

---

## Klaytn

**Chain Type**: L1, EVM-compatible (Kakao-backed)  
**Consensus**: PBFT + PoS  
**Smart Contract Language**: Solidity  

### Major Exploits

| Date | Protocol | Loss | Vulnerability |
|------|----------|------|---------------|
| 2022 | **KlaySwap** | $1.9M | Frontend Compromise |

### KlaySwap (2022) - $1.9M
- **Vector**: Frontend compromise (malicious script injected)
- **Classification**: Frontend & Infrastructure / Frontend Compromise

---

## Flow

**Chain Type**: L1, Resource-oriented (Cadence)  
**Consensus**: HotStuff (PoS)  
**Smart Contract Language**: Cadence  

### Major Exploits

| Date | Protocol | Loss | Vulnerability |
|------|----------|------|---------------|
| 2024 | **Flow** | $3.9M | Token Accounting / Infinite Mint |

### Flow (2024) - $3.9M
- **Vector**: Infinite mint via token accounting flaw
- **Classification**: Token & Share Accounting / Infinite Mint

---

## Cardano

**Chain Type**: L1, eUTXO model  
**Consensus**: Ouroboros (PoS)  
**Smart Contract Language**: Plutus (Haskell), Aiken, Marlowe  

### Major Exploits

| Date | Protocol | Loss | Vulnerability |
|------|----------|------|---------------|
| 2024 | **Wanchain** | $10M | Input Validation / Signature Verification Flaw |

### Wanchain (2024) - $10M
- **Vector**: Signature verification flaw in bridge
- **Classification**: Input Validation / Signature Verification Flaw
- **Note**: Cardano used as bridge chain; Wanchain is separate but bridge-related

---

## Stacks

**Chain Type**: L1.5 (Bitcoin-secured), Clarity  
**Consensus**: PoX (Proof of Transfer)  
**Smart Contract Language**: Clarity (decidable, no reentrancy)  

### Major Exploits

| Date | Protocol | Loss | Vulnerability |
|------|----------|------|---------------|
| 2024 | **ALEX** | $4.3M | Social Engineering / Phishing |

### ALEX (2024) - $4.3M
- **Vector**: Phishing attack on bridge
- **Classification**: Social Engineering / Phishing

---

## Sei

**Chain Type**: L1, Sector-specific (trading), Twin-Turbo consensus  
**Consensus**: Twin-Turbo (optimistic + fast finality)  
**Smart Contract Language**: CosmWasm (Rust/WASM), EVM (Sei v2)  

### Major Exploits
- **No major exploits yet** (launched 2023)

### Sei-Specific Risk Factors
1. **CosmWasm**: Rust/WASM; memory safe but logic bugs
2. **EVM Compatibility (v2)**: Inherits EVM risks
3. **Orderbook Module**: Native orderbook = new attack surface
4. **Parallel Execution**: Concurrency bugs

---

## Monad

**Chain Type**: L1, EVM-compatible, parallel execution  
**Consensus**: MonadBFT  
**Smart Contract Language**: Solidity (EVM bytecode)  

### Status
- **Testnet**: 2024
- **Mainnet**: Not yet launched (2025 target)
- **No exploits** (pre-mainnet)

### Monad-Specific Risk Factors
1. **Parallel EVM**: Optimistic parallel execution = new concurrency bugs
2. **Deferred Execution**: Execution delayed after consensus
3. **New Client**: Custom Rust client = new implementation bugs

---

## Berachain

**Chain Type**: L1, EVM-compatible, Proof of Liquidity  
**Consensus**: Proof of Liquidity (PoL)  
**Smart Contract Language**: Solidity  

### Status
- **Testnet**: 2024
- **Mainnet**: Not yet launched
- **No exploits** (pre-mainnet)

### Berachain-Specific Risk Factors
1. **Proof of Liquidity**: Novel consensus; validator incentives tied to liquidity
2. **Precompiles**: Native DEX, oracle, voting precompiles
3. **Tri-token Model**: BERA, BGT, HONEY = complex economics

---

## Cross-Cutting Themes for Emerging L1s

### 1. New VM = New Bug Classes
| VM | Novel Risk |
|----|------------|
| Move (Sui/Aptos) | Package upgrade governance, resource logic |
| WASM (Near/Sei) | Memory safety ≠ logic safety; host function bugs |
| Cadence (Flow) | Resource ownership, capability security |
| Clarity (Stacks) | Decidability limits expressiveness |
| Parallel EVM (Monad/Sei v2) | Concurrency, state conflicts |

### 2. Bridge Dependency
- Most emerging L1s rely on bridges (Wormhole, LayerZero, custom)
- Bridge exploits = largest single loss category
- **Recommendation**: Minimize bridge trust; use light clients

### 3. Low Audit Coverage
- Few auditors for Move, Cadence, Clarity, CosmWasm
- **Recommendation**: Invest in language-specific audit tooling

### 4. Upgrade Governance Risk
- Most new L1s have on-chain upgrade mechanisms
- **Risk**: Malicious upgrade, governance attack
- **Mitigation**: Timelocks, multisig, emergency pause

---

## Summary Table

| Chain | VM | Major Exploit | Top Risk |
|-------|-----|---------------|----------|
| Sui | Move | Cetus $223M | Arithmetic/CLMM math |
| Aptos | Move | None yet | Low audit coverage |
| Tron | EVM | HTX $100M+ | Key compromise, centralization |
| Near | WASM | Moola $8.4M | Oracle, sharding complexity |
| Celo | EVM | Moola $8.4M | Oracle manipulation |
| Klaytn | EVM | KlaySwap $1.9M | Frontend compromise |
| Flow | Cadence | Flow $3.9M | Token accounting |
| Cardano | Plutus | Wanchain $10M | Bridge sig verification |
| Stacks | Clarity | ALEX $4.3M | Phishing/bridge |
| Sei | CosmWasm/EVM | None yet | Parallel + orderbook |
| Monad | Parallel EVM | Pre-mainnet | Concurrency bugs |
| Berachain | EVM+PoL | Pre-mainnet | Novel consensus |

---

## References

- [DefiLlama Hacks - All Chains](https://defillama.com/hacks)
- [Cetus Hack Analysis](https://defillama.com/hack/cetus-clmm)
- [Move Prover Documentation](https://move-language.github.io/move/prover.html)
- [Solana Wormhole Hack](https://wormhole.com/blog/wormhole-incident-report)
- [Mango Markets DOJ Case](https://www.justice.gov/usao-sdny/pr/manhattan-u-s-attorney-announces-charges-against-avraham-eisenberg)
- [Monad Documentation](https://monad.xyz/docs)
- [Berachain Documentation](https://docs.berachain.com/)