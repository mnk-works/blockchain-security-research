# Research Methodology

## Data Sources

### Primary: DeFiLlama Hacks Database
- **API**: `https://api.llama.fi/hacks`
- **Fields**: date, name, classification, technique, amount, chain, bridgeHack, targetType, returnedFunds, defillamaId, parentProtocolId, language
- **Coverage**: 540+ incidents (2016-present)
- **Filtering**: `targetType == "DeFi Protocol"` for protocol exploits; includes bridges, CEX, wallet, chain
- **Limitations**: 
  - Community-reported, may miss minor exploits
  - Classification sometimes broad (e.g., "Protocol Logic" catch-all)
  - Amounts may be estimated
  - Chain field: multiple chains per incident possible

### Secondary: Security Firm Reports
- **Hacken**: Quarterly reports, vulnerability classifications
- **CertiK**: Monthly alerts, Skynet ratings
- **PeckShield**: Incident analyses, exploit details
- **BlockSec**: Phalcon analysis, transaction traces
- **Halborn**: Deep-dive exploit explanations
- **Immunefi**: Bug bounty payouts by severity/class
- **Trail of Bits**: Research publications
- **OpenZeppelin**: Security advisories

### Tertiary: Academic & Industry Research
- **OWASP Smart Contract Top 10** (2023, 2025)
- **Academic papers**: ICSE, CCS, USENIX Security, Financial Cryptography
- **Conference talks**: Devcon, EthCC, SmartCon, zkSummit
- **Blog posts**: samczsun, rekt.news, various security researchers

### On-Chain Verification
- **Block explorers**: Etherscan, BscScan, Arbiscan, PolygonScan, SnowTrace, Solscan, FTMScan
- **Transaction analysis**: Tenderly, Phalcon, Foundry trace
- **Contract verification**: Source code verification status

---

## Data Collection Process

### 1. DeFiLlama API Pull
```bash
curl -s "https://api.llama.fi/hacks" > defillama_hacks.json
```
- Timestamp: October 28, 2026
- 540+ records
- Parsed for chain-specific analysis

### 2. Chain Filtering
```python
# For each chain, filter incidents where chain array includes target chain
ethereum_hacks = [h for h in hacks if "Ethereum" in h["chain"]]
bsc_hacks = [h for h in hacks if "BSC" in h["chain"] or "Binance Smart Chain" in h["chain"]]
# ... etc
```

### 3. Classification Normalization
DeFiLlama classifications mapped to OWASP SC categories:
| DeFiLlama Classification | OWASP SC Category |
|-------------------------|-------------------|
| Protocol Logic | SC03:2025 Logic Errors |
| Access Control | SC01:2025 Access Control |
| Reentrancy | SC05:2025 Reentrancy |
| Oracle Manipulation | SC02:2025 Oracle Manipulation |
| Token & Share Accounting | Token Accounting |
| Bridge & Cross-Chain | Bridge Logic |
| Key Compromise | Access Control / OpSec |
| Frontend & Infrastructure | Frontend |
| Governance | Governance |
| Rugpull | Rugpull |
| Social Engineering | Social Engineering |
| Input Validation | SC04:2025 Input Validation |

### 4. Manual Enrichment
For major exploits (>$1M):
- Root cause analysis from post-mortems
- Transaction trace verification
- Contract code review (if verified)
- Vulnerability variant identification

---

## Analysis Framework

### Per-Chain Analysis Structure
1. **Overview**: Chain type, consensus, TVL, language
2. **Major Exploits Table**: Date, protocol, loss, vulnerability, root cause
3. **Vulnerability Breakdown**: Count and value by classification
4. **Chain-Specific Risk Factors**: Architecture, ecosystem patterns
5. **Attack Vector Analysis**: Deep dive on top 3-5 vectors
6. **Ease of Exploitation Assessment**: Table with technical difficulty, capital, chain factors
7. **Notable Incidents Deep Dive**: 2-3 major cases with technical details
8. **Tooling & Detection**: Chain-specific + general EVM tools
9. **Recent Trends**: 2024-2025 observations
10. **Recommendations**: Chain-specific mitigation advice

### Vulnerability Class Analysis Structure
1. **Definition & Variants**: Code examples for each variant
2. **Major Real-World Cases**: Table with date, protocol, chain, loss, variant
3. **Detection Methods**: Static, dynamic, formal, runtime
4. **Mitigation Patterns**: Code examples for each mitigation
5. **Ease of Exploitation Assessment**: Standardized table
6. **Why It Persists**: Root causes for continued prevalence
7. **Testing Strategies**: Unit, invariant, fuzzing, formal
8. **References**: Primary sources

---

## Limitations & Biases

### Data Limitations
1. **Reporting Bias**: Major exploits over-reported; minor exploits (<$100K) under-reported
2. **Attribution Bias**: Some exploits misclassified (e.g., key compromise vs access control)
3. **Chain Bias**: Ethereum over-represented due to tooling maturity
4. **Time Lag**: Recent exploits may not be in database yet
5. **Amount Accuracy**: Reported amounts may differ from actual losses

### Methodological Biases
1. **Survivorship Bias**: Only exploited protocols analyzed; secure protocols not studied
2. **Recency Bias**: 2024-2025 data weighted more heavily
3. **Language Bias**: English sources only; may miss non-English reports
4. **Tooling Bias**: EVM chains better analyzed due to tooling availability

### Mitigation of Biases
- Cross-reference multiple sources (DeFiLlama + security firms + on-chain)
- Explicitly note uncertainty in classifications
- Include "unknown" or "unclassified" categories
- Version all data with timestamps

---

## Reproducibility

### Data Files
- `data/defillama_hacks.json` - Raw API pull (Oct 28, 2026)
- `data/exploit-timeline.csv` - Processed timeline (generated)

### Analysis Scripts
```python
# Chain analysis
python analyze_chain.py --chain Ethereum --data defillama_hacks.json

# Vulnerability trends
python analyze_trends.py --start 2023-01-01 --end 2025-10-28

# Cross-chain comparison
python compare_chains.py --chains Ethereum,BSC,Arbitrum,Optimism,Base
```

### Updates
- **Quarterly**: Refresh DeFiLlama data, update trends
- **Per Major Exploit**: Add to relevant chain/vulnerability docs within 1 week
- **Annual**: Full methodology review, bias assessment

---

## Ethical Guidelines

1. **Defensive Focus**: All research for protection, not exploitation
2. **No Zero-Day Disclosure**: No unpublished vulnerabilities detailed
3. **Responsible Disclosure**: If new vuln found, report to protocol first
4. **No Exploit Code**: No functional exploit code in repository
5. **Attribution**: All sources cited; no proprietary data
6. **Educational Purpose**: Enable developers to build secure systems

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2026-10-28 | Initial release with 9 chains, 6 vulnerability classes, 3 analysis docs |

---

## Contact & Contributions

- **Issues**: Report data errors, missing exploits, classification corrections
- **PRs**: Additional chains, vulnerability classes, analysis improvements
- **Standards**: Follow existing structure and citation format