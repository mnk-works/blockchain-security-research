# Solana Security Analysis

**Chain Type**: L1, Non-EVM (SVM - Solana Virtual Machine)  
**Native Token**: SOL  
**Consensus**: Proof of History + Proof of Stake (Tower BFT)  
**Smart Contract Language**: Rust (BPF bytecode), C/C++  
**Account Model**: Account-based (not UTXO), programs = contracts  
**Total Value Locked (DeFi)**: ~$1.5B+ (2024)  
**Total Exploits (DeFiLlama)**: 10+  
**Total Value Lost**: $350M+

---

## Major Exploits (2023-2025)

### 2025

| Date | Protocol | Loss | Vulnerability | Root Cause |
|------|----------|------|---------------|------------|
| 2025 | **Drift Trade** | $309.5M | Bridge / Cross-Chain | Cross-chain exploit (Drift v2 perp DEX) |
| 2025 | **Upbit (Solana)** | $36M | Key Compromise / Malware | Hot wallet malware |

### 2024

| Date | Protocol | Loss | Vulnerability | Root Cause |
|------|----------|------|---------------|------------|
| 2024 | **Synthetify** | $230K | Governance / Malicious Proposal | Governance attack |

### 2023

| Date | Protocol | Loss | Vulnerability | Root Cause |
|------|----------|------|---------------|------------|
| 2023 | **Bonfida / Solend** | Various | Access Control | Improper access control |
| 2023 | **Monkey Kingdom** | $1.3M | Social Engineering / Phishing | NFT phishing |

### Historical Major Exploits

| Date | Protocol | Loss | Vulnerability |
|------|----------|------|---------------|
| 2022 | **Wormhole Bridge** | $325M | Bridge Logic / Signature Verification | Missing signature verification on `verifySignatures` |
| 2022 | **Crema Finance** | ~$8M | Flash Loan / Oracle | Flash loan + oracle manipulation |
| 2022 | **Cashio** | ~$52M | Infinite Mint | Token accounting flaw (infinite mint) |
| 2022 | **Mango Markets** | ~$116M | Oracle Manipulation | Mango perp market oracle manipulation |
| 2022 | **Slope Wallet** | ~$8M | Key Compromise | Private key extraction from logs |
| 2021 | **Various** | Various | Various | Early ecosystem exploits |

---

## Vulnerability Breakdown (Solana)

| Classification | Count | Total Lost | Notable Examples |
|----------------|-------|------------|------------------|
| Bridge & Cross-Chain | ~2 | $630M+ | Wormhole ($325M), Drift ($309M) |
| Oracle Manipulation | ~2 | $116M+ | Mango Markets, Crema |
| Token & Share Accounting | ~2 | $52M+ | Cashio (infinite mint) |
| Key Compromise | ~3 | $44M+ | Upbit, Slope, various |
| Access Control | ~2 | $10M+ | Solend, Bonfida |
| Governance | ~1 | $230K | Synthetify |
| Social Engineering | ~1 | $1.3M | Monkey Kingdom |

---

## Solana-Specific Risk Factors

### 1. Account Model (Not EVM)
- **Accounts**: Data + lamports (SOL), owned by programs
- **Programs**: Stateless, deployed to accounts
- **PDAs**: Program Derived Addresses (deterministic, no private key)
- **Rent**: Accounts must be rent-exempt (2 years of rent)
- **Risk**: Different vulnerability classes than EVM (no reentrancy in same way)

### 2. No Native Reentrancy (But Cross-Program Invocation Risks)
- **CPI**: Cross-Program Invocation (like `delegatecall` but explicit)
- **Reentrancy**: Possible via CPI cycles but harder; requires explicit program design
- **Pattern**: "Reentrancy" on Solana = CPI cycle with state inconsistency

### 3. Bridge Heavy Ecosystem
- **Wormhole**: Major Ethereum↔Solana bridge ($325M hack)
- **Solana ↔ Other L1s**: Multiple bridges = multiple attack surfaces
- **Drift (2025)**: Cross-chain perp DEX exploited

### 4. Token Program Standards
- **SPL Token**: Standard fungible token (like ERC-20)
- **Token-2022**: New standard with extensions (confidential transfers, etc.)
- **Risk**: Custom token programs = custom bugs (Cashio infinite mint)

### 5. Anchor Framework
- **Standard**: Most Solana programs use Anchor (Rust framework)
- **Benefits**: Auto-generated IDL, accounts validation, tests
- **Risk**: Macro complexity can hide bugs; version mismatches

---

## Attack Vector Analysis

### 1. Bridge Exploits (Dominant by Value - $630M+)
**Wormhole (Feb 2022) - $325M**:
- **Root Cause**: Missing signature verification in `verify_signatures` syscall
- **Mechanism**: 
  1. Attacker created fake "guardian" signatures
  2. Submitted to `post_vaa` (verify and post Verified Action Approval)
  3. Contract didn't verify signatures properly
  4. Minted 120k wETH on Solana
- **Classification**: Bridge & Cross-Chain / Bridge Logic Flaw
- **Fix**: Proper signature verification; guardian set upgrades

**Drift Trade (2025) - $309.5M**:
- **Protocol**: Drift v2 (perp DEX with cross-chain)
- **Vector**: Cross-chain exploit
- **Classification**: Bridge & Cross-Chain

### 2. Oracle Manipulation (Mango Markets - $116M)
**Mango Markets (Oct 2022) - $116M**:
- **Protocol**: Mango (perp DEX + lending)
- **Vector**: Oracle manipulation on MNGO perp market
- **Mechanism**:
  1. Large MNGO position opened
  2. Manipulated MNGO price on Mango's oracle (thin orderbook)
  3. Inflated collateral value
  4. Borrowed all other assets
- **Root Cause**: Custom oracle (not Chainlink/Pyth); thin perp market
- **Classification**: Oracle Manipulation

**Crema Finance (2022) - ~$8M**:
- **Vector**: Flash loan + oracle manipulation on concentrated liquidity

### 3. Token Accounting / Infinite Mint (Cashio - $52M)
**Cashio (Mar 2022) - $52M**:
- **Protocol**: Cashio (stablecoin protocol)
- **Vector**: Infinite mint via token account validation flaw
- **Mechanism**:
  1. `cashio` program minted CASH stablecoin
  2. Validation of collateral accounts flawed
  3. Attacker created fake collateral accounts
  4. Minted 2B+ CASH unbacked
- **Root Cause**: Missing account ownership/validation checks
- **Classification**: Token & Share Accounting / Infinite Mint

### 4. Key Compromise (Slope Wallet - $8M, Upbit - $36M)
**Slope Wallet (Aug 2022) - ~$8M**:
- **Vector**: Private keys logged to Sentry (error tracking)
- **Mechanism**: Mobile app sent mnemonics to Sentry in crash reports
- **Impact**: 9000+ wallets drained
- **Classification**: Key Compromise / Frontend & Infrastructure

**Upbit (2025) - $36M**:
- **Vector**: Malware on hot wallet infrastructure
- **Classification**: Key Compromise / Malware

### 5. Access Control (Solend, Bonfida)
**Solend (2023) - $16K**:
- **Vector**: Improper access control on admin functions
- **Classification**: Access Control / Improper Access Control

---

## Ease of Exploitation Assessment (Solana)

| Vulnerability | Technical Difficulty | Capital Required | Solana-Specific Factors |
|---------------|---------------------|------------------|-------------------------|
| Bridge Logic | High | High | **High value**: Wormhole, Drift major targets |
| Oracle Manipulation | Medium | Medium | **Easier**: Custom oracles common, thin perp markets |
| Token Accounting | Medium | Low | **Unique**: Account validation flaws (Cashio) |
| Key Compromise | N/A (OpSec) | N/A | **High**: Sentry logging, malware |
| Access Control | Low | Low | Anchor macros can hide missing checks |
| CPI Reentrancy | High | Low | Harder than EVM; explicit CPI required |

---

## Notable Incidents Deep Dive

### Wormhole Bridge (Feb 2022) - $325M
- **Protocol**: Wormhole (generic message passing bridge)
- **Root Cause**: `verify_signatures` syscall not properly verifying guardian signatures
- **Code Flaw**: 
  ```rust
  // Vulnerable: didn't verify signatures against guardian set
  verify_signatures(vaa.signatures, guardian_set)?;
  ```
- **Exploit**: Attacker submitted VAA with fake signatures → minted 120k wETH
- **Recovery**: Jump Crypto covered losses; wormhole upgraded
- **Lesson**: **Bridge signature verification must be formally verified**

### Mango Markets (Oct 2022) - $116M
- **Protocol**: Mango (perp DEX + lending on Solana)
- **Attacker**: Avraham Eisenberg (arrested, convicted 2024)
- **Mechanism**: 
  1. $5M USDC → large MNGO-PERP position
  2. Manipulated MNGO oracle price 10x via thin orderbook
  3. $116M borrowed against inflated collateral
- **Legal**: First major DeFi criminal prosecution (wire fraud, commodities manipulation)
- **Lesson**: **Custom oracles on thin markets = manipulation magnet**

### Cashio (Mar 2022) - $52M
- **Protocol**: Cashio (algorithmic stablecoin)
- **Root Cause**: Missing `owner` check on collateral accounts
- **Code Flaw**: Program didn't verify `account.owner == spl_token::id()`
- **Exploit**: Fake SPL token accounts → mint unbacked CASH
- **Lesson**: **Always validate account ownership and data**

### Slope Wallet (Aug 2022) - ~$8M
- **Vector**: Private keys in Sentry crash logs
- **Root Cause**: Mobile SDK logged mnemonics to error tracking
- **Impact**: 9000+ wallets, ~$8M drained
- **Lesson**: **Never log secrets; audit third-party SDKs**

---

## Solana-Specific Vulnerability Classes

### 1. Missing Account Validation
- **Pattern**: Program accepts account without checking owner, data, or rent-exemption
- **Examples**: Cashio (infinite mint), multiple token program bugs
- **Mitigation**: Anchor `#[account(...)]` constraints; manual checks

### 2. PDA (Program Derived Address) Misuse
- **Pattern**: Incorrect seeds, missing bump verification, PDA collision
- **Mitigation**: Explicit bump seeds; `Pubkey::find_program_address`

### 3. CPI (Cross-Program Invocation) Issues
- **Reentrancy via CPI**: Program A → Program B → Program A (state inconsistency)
- **Signer privilege escalation**: Incorrect signer passing in CPI
- **Mitigation**: Reentrancy guards (account locks); explicit signer handling

### 4. Arithmetic Over/Underflow
- **Rust**: Panics on overflow in debug; wraps in release (unless checked)
- **Anchor**: Uses `checked_*` by default
- **Risk**: Custom math without checked ops

### 5. Rent Exemption / Account Closure
- **Pattern**: Account closed but references remain; rent not collected
- **Mitigation**: Proper `close` constraints; rent-exempt checks

---

## Tooling & Detection on Solana

### Solana-Specific
- **Solscan / SolanaFM / Explorer** - Block explorers
- **Anchor** - Framework with built-in validation
- **Solana Program Library (SPL)** - Audited standard programs
- **SBF/BPF Analysis Tools** - `solana-verify`, `cargo-contract`

### Security Tools
- **Sec3 (formerly Soteria)** - Static analysis for Solana
- **Neodyme** - Audit firm, tooling
- **OtterSec** - Audit firm, Solana focus
- **Fuzzers**: `cargo-fuzz`, `trident` (Anchor fuzzing)
- **Formal Verification**: `solana-verifier` (early stage)

### Monitoring
- **Helius** - Enhanced RPC, webhooks
- **Triton** - RPC with analytics
- **Forta** - Limited Solana support

---

## Recent Trends (2024-2025)

1. **Bridge exploits dominate value** (Wormhole, Drift = $630M+)
2. **Token-2022 adoption** - New standard, new attack surface
3. **Compressed NFTs / State Compression** - New account model, new risks
4. **Jito MEV** - Different MEV landscape (no PBS, block engine)
5. **Mobile wallet security** - Slope lesson; better SDK practices
6. **Anchor 0.29+ / 0.30** - Breaking changes, migration risks

---

## Recommendations for Solana Developers

1. **Use Anchor with full account constraints** - `#[account(mut, has_one = owner, ...)]`
2. **Validate ALL account inputs** - Owner, data, rent-exemption, PDA seeds
3. **Use SPL Token / Token-2022** - Don't write custom token programs
4. **Use Pyth/Chainlink oracles** - Not custom thin-market oracles
5. **Audit bridges extensively** - Wormhole showed $325M risk
6. **Never log secrets** - Slope wallet lesson
7. **Fuzz test with Trident** - Anchor-native fuzzing
8. **Reentrancy guards for CPI cycles** - Account-based locks
9. **Test on devnet/testnet with mainnet-like conditions**

---

## References

- [DefiLlama Hacks - Solana](https://defillama.com/hacks?chain=Solana)
- [Wormhole Hack Analysis](https://wormhole.com/blog/wormhole-incident-report)
- [Mango Markets Exploit - DOJ](https://www.justice.gov/usao-sdny/pr/manhattan-u-s-attorney-announces-charges-against-avraham-eisenberg)
- [Cashio Exploit Analysis](https://twitter.com/samczsun/status/1506472834518224896)
- [Slope Wallet Post-Mortem](https://blog.slope.finance/slope-wallet-security-incident-post-mortem)
- [Solana Security Best Practices](https://solana.com/developers/guides/security)
- [Anchor Book - Security](https://book.anchor-lang.com/anchor_in_depth/security.html)
- [Drift Hack 2025](https://defillama.com/hack/drift-trade)