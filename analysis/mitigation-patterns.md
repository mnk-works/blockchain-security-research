# Mitigation Patterns & Best Practices

Comprehensive defense strategies for smart contract security.

---

## Defense in Depth Layers

```
┌─────────────────────────────────────────────────────────────┐
│  LAYER 5: INCIDENT RESPONSE                                 │
│  Pause, migrate, negotiate, law enforcement                │
├─────────────────────────────────────────────────────────────┤
│  LAYER 4: RUNTIME MONITORING                                │
│  Forta, Tenderly, custom alerts, anomaly detection         │
├─────────────────────────────────────────────────────────────┤
│  LAYER 3: DESIGN PATTERNS                                   │
│  CEI, ReentrancyGuard, RBAC, Timelock, Circuit Breakers   │
├─────────────────────────────────────────────────────────────┤
│  LAYER 2: TESTING & VERIFICATION                            │
│  Unit, Integration, Invariant, Fuzzing, Formal Verification│
├─────────────────────────────────────────────────────────────┤
│  LAYER 1: SECURE DEVELOPMENT LIFECYCLE                      │
│  Threat modeling, code review, audit, CI/CD security       │
└─────────────────────────────────────────────────────────────┘
```

---

## Layer 1: Secure Development Lifecycle

### 1.1 Threat Modeling (Pre-Code)
```
For each function:
1. What assets does it touch?
2. Who can call it? (roles, conditions)
3. What external calls does it make?
4. What state changes occur? (order!)
5. What invariants must hold?
6. How can it be abused? (reentrancy, front-run, oracle, etc.)
```

**Tools**: Microsoft Threat Modeling Tool, custom checklists

### 1.2 Code Review Requirements
- **Mandatory**: 2+ reviewers for all state-changing functions
- **Checklist**: CEI, access control, input validation, overflow, external calls
- **Tools**: GitHub PR templates, Slither in CI

### 1.3 Audit Strategy
| Protocol Stage | Audit Approach |
|----------------|----------------|
| Pre-mainnet | 2+ firms, different methodologies (static + manual + formal) |
| Major upgrade | Full re-audit or delta audit |
| Parameter change | Light review + timelock |
| Emergency fix | Fast-track + post-deploy audit |

**Firm Diversity**: At least one formal verification firm (Certora, Runtime Verification)

### 1.4 CI/CD Security Gates
```yaml
# .github/workflows/security.yml
jobs:
  security:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Slither
        uses: crytic/slither-action@v1
      - name: Solhint
        run: solhint contracts/**/*.sol
      - name: Foundry Test
        run: forge test --invariant --invariant-runs 1000
      - name: Echidna
        run: echidna-test . --config echidna.yaml
```

---

## Layer 2: Testing & Verification

### 2.1 Unit Testing (100% Coverage Target)
```solidity
// Every public/external function tested
// Edge cases: 0, 1, max, precision boundaries
// Failure cases: revert messages, error codes
```

### 2.2 Integration Testing
- Full user flows: deposit → borrow → repay → withdraw
- Cross-contract interactions
- Upgrade scenarios

### 2.3 Invariant Testing (Foundry) - CRITICAL
```solidity
// Run: forge test --invariant --invariant-runs 50000 --invariant-depth 100
contract InvariantTest is Test {
    Protocol protocol;
    
    function setUp() public { protocol = new Protocol(); }
    
    // CORE INVARIANTS (must NEVER break)
    invariant totalAssetsEqualLiabilities() {
        assertGe(protocol.totalAssets(), protocol.totalLiabilities());
    }
    
    invariant noNegativeBalances() {
        for (address user : users) {
            assertGe(protocol.balanceOf(user), 0);
        }
    }
    
    invariant accessControlEnforced() {
        // Only authorized roles can call admin functions
        // Verified via fuzzing
    }
    
    invariant priceDeviationLimited() {
        // Oracle price cannot change > X% per block
        assertLe(priceChangePercent(), MAX_BLOCK_DEVIATION_BPS);
    }
    
    invariant roundingFavorsProtocol() {
        // Fees round UP, rewards round DOWN
    }
}
```

### 2.4 Fuzzing (Echidna/Medusa/Trident)
```yaml
# echidna.yaml
testMode: "assertion"
testLimit: 500000
timeout: 600
corpusDir: "corpus"
```

### 2.5 Formal Verification (Certora/Halmos)
```cvl
// Certora CVL - Proves properties for ALL inputs
// Rules for:
// - No reentrancy
// - Access control enforcement
// - Accounting invariants
// - Oracle price bounds
// - Upgrade safety
```

**When to use**: Core contracts (lending, AMM, bridge, token)

---

## Layer 3: Design Patterns

### 3.1 Checks-Effects-Interactions (CEI) - MANDATORY
```solidity
function withdraw(uint amount) external {
    // 1. CHECKS
    require(balances[msg.sender] >= amount, "Insufficient balance");
    require(!paused, "Paused");
    
    // 2. EFFECTS (state changes)
    balances[msg.sender] -= amount;
    totalSupply -= amount;
    
    // 3. INTERACTIONS (external calls)
    (bool success, ) = msg.sender.call{value: amount}("");
    require(success, "Transfer failed");
}
```

### 3.2 ReentrancyGuard (All Entry Points)
```solidity
// OpenZeppelin ReentrancyGuard
// Apply to: ALL public/external functions making external calls
modifier nonReentrant() {
    _entered = true;
    _;
    _entered = false;
}
```

### 3.3 Role-Based Access Control (RBAC)
```solidity
// OpenZeppelin AccessControl
bytes32 public constant MINTER_ROLE = keccak256("MINTER_ROLE");
bytes32 public constant PAUSER_ROLE = keccak256("PAUSER_ROLE");
bytes32 public constant ADMIN_ROLE = keccak256("ADMIN_ROLE");
bytes32 public constant UPGRADER_ROLE = keccak256("UPGRADER_ROLE");

// NEVER use onlyOwner for production protocols
```

### 3.4 TimelockController (All Admin Actions)
```solidity
// OpenZeppelin TimelockController
// Minimum 24h delay (48h+ recommended)
// All parameter changes, upgrades, emergency actions
TimelockController timelock = TimelockController(
    2 days,  // min delay
    [],      // proposers
    [],      // executors
    admin    // admin
);
```

### 3.5 Circuit Breakers / Pause
```solidity
// OpenZeppelin Pausable
// Pause on: price deviation, unusual volume, known exploit pattern
function pause() external onlyRole(PAUSER_ROLE) { _pause(); }
function unpause() external onlyRole(ADMIN_ROLE) { _unpause(); }

// Auto-pause triggers:
function checkPriceDeviation(uint newPrice) internal {
    uint deviation = calculateDeviation(newPrice, lastPrice);
    if (deviation > AUTO_PAUSE_THRESHOLD_BPS) {
        _pause();
        emit AutoPaused(deviation);
    }
}
```

### 3.6 Supply Caps & Rate Limits
```solidity
// Per-market supply caps (prevents donation attack scaling)
mapping(address => uint) public supplyCap;

function setSupplyCap(address market, uint cap) external onlyRole(ADMIN_ROLE) {
    supplyCap[market] = cap;
}

function mint(uint amount) external {
    require(totalSupply() + amount <= supplyCap[market], "Cap exceeded");
    // ...
}
```

### 3.7 Oracle Best Practices
```solidity
// 1. Primary: Chainlink/Pyth
// 2. Fallback: TWAP (30+ min)
// 3. Aggregation: Median of multiple sources
// 4. Circuit breaker: Max deviation per block
// 5. Supply caps on new markets

function getPrice() external view returns (uint) {
    uint chainlinkPrice = getChainlinkPrice();
    uint twapPrice = getTWAPPrice();
    uint pythPrice = getPythPrice();
    
    // Median
    uint[] memory prices = [chainlinkPrice, twapPrice, pythPrice];
    sort(prices);
    uint median = prices[1];
    
    // Deviation check
    require(deviation(median, lastPrice) < MAX_DEVIATION_BPS, "Price spike");
    
    return median;
}
```

### 3.8 Donation Attack Prevention (Compound Forks)
```solidity
// 1. Supply caps
// 2. Max exchange rate change per block (1-2%)
// 3. Detect direct transfers to vToken
// 4. Consider Compound v3 (Comet) - not vulnerable

uint public constant MAX_EXCHANGE_RATE_CHANGE_BPS = 100; // 1%
uint public lastExchangeRate;

function exchangeRateCurrent() public view returns (uint) {
    uint rate = (totalCash + totalBorrows - totalReserves) * 1e18 / totalSupply;
    require(rate <= lastExchangeRate * (10000 + MAX_EXCHANGE_RATE_CHANGE_BPS) / 10000);
    return rate;
}

// Monitor for donations
receive() external payable {
    // Detect and log/revert large direct transfers
    if (msg.value > DONATION_THRESHOLD) {
        emit DonationDetected(msg.sender, msg.value);
        // Option: revert, or limit impact
    }
}
```

---

## Layer 4: Runtime Monitoring

### 4.1 Forta Network
```javascript
// Custom Forta bot
// Detects: reentrancy, large transfers, price deviation, pause events
// Alert channels: Slack, Discord, PagerDuty, Telegram
```

### 4.2 Tenderly Alerts
- Balance changes > threshold
- Function calls to admin functions
- Contract upgrades
- Failed transactions (revert analysis)

### 4.3 Custom Monitoring
```solidity
// On-chain event emission for off-chain indexing
event Withdrawal(address indexed user, uint amount, uint timestamp);
event PriceUpdate(uint oldPrice, uint newPrice, uint deviation);
event AccessControlViolation(address caller, string functionName);
event CircuitBreakerTriggered(string reason);
```

### 4.4 Anomaly Detection
- ML-based: unusual gas usage, call patterns, value transfers
- Rule-based: known exploit signatures

---

## Layer 5: Incident Response

### 5.1 Incident Response Plan
```
1. DETECT (Monitoring alerts)
2. ASSESS (Severity, scope, funds at risk)
3. PAUSE (Emergency pause if possible)
4. CONTAIN (Block attacker, freeze if upgradeable)
4. COMMUNICATE (Users, partners, exchanges, law enforcement)
5. INVESTIGATE (Root cause, forensic analysis)
6. RECOVER (Migrate, compensate, upgrade)
7. POST-MORTEM (Public report, lessons learned)
```

### 5.2 Emergency Procedures
- **Pause keys**: Distributed multisig (3/5), hardware wallets
- **Upgrade keys**: Timelock + multisig (separate from pause)
- **Communication**: Pre-drafted templates, verified channels
- **Legal**: Law enforcement contacts, exchange freeze contacts

### 5.3 Post-Exploit Checklist
- [ ] Root cause identified and patched
- [ ] All similar patterns audited across codebase
- [ ] Monitoring rules added for exploit signature
- [ ] Incident report published
- [ ] Users compensated (if possible)
- [ ] Security budget increased

---

## Protocol-Type Specific Mitigations

### Lending Protocols
- Donation attack prevention (supply caps, rate limits, Comet)
- Oracle manipulation resistance (Chainlink + TWAP + caps)
- Liquidation engine: max liquidation discount, batch liquidations
- Isolation mode for risky assets

### DEX/AMM
- Reentrancy guards on all swap/liquidity functions
- Fee-on-transfer token handling
- Concentrated liquidity: tick math verification
- MEV protection: private mempool, batch auctions

### Bridges
- Formal verification of message verification
- Light client validation (not just multisig)
- Rate limits on withdrawals
- Emergency pause per chain

### Yield/Optimizers
- Strategy isolation (each strategy separate contract)
- Harvest limits
- Emergency exit per strategy
- Reentrancy guards on deposit/withdraw

### Governance
- Timelock on all proposals
- Voting power snapshots (not current balance)
- Quorum requirements
- Veto council for emergency

---

## Tooling Summary

| Category | Tools | Integration |
|----------|-------|-------------|
| **Static Analysis** | Slither, Mythril, Securify, Solhint | CI/CD |
| **Dynamic/Fuzzing** | Foundry, Echidna, Medusa, Trident | CI/CD + local |
| **Formal Verification** | Certora, Halmos, K Framework | Pre-deploy |
| **Testing** | Foundry, Hardhat, Waffle | CI/CD |
| **Monitoring** | Forta, Tenderly, Phalcon, Helius | Production |
| **Debugging** | Tenderly, Phalcon, Foundry trace | Post-incident |
| **Audit** | Multiple firms, different methods | Pre-deploy + upgrades |

---

## Security Budget Guidelines

| Protocol TVL | Annual Security Budget | Allocation |
|--------------|------------------------|------------|
| <$1M | $50K-100K | 1 audit, basic monitoring |
| $1M-$10M | $100K-300K | 2 audits, invariant testing, monitoring |
| $10M-$100M | $300K-1M | 3+ audits, formal verification, dedicated monitoring |
| $100M-$1B | $1M-3M | Continuous auditing, bug bounty ($1M+), red team |
| >$1B | 1-2% of TVL | Full security program, dedicated team |

---

## References

- [OpenZeppelin Contracts](https://github.com/OpenZeppelin/openzeppelin-contracts)
- [Foundry Book - Testing](https://book.getfoundry.sh/forge/tests.html)
- [Certora Documentation](https://docs.certora.com/)
- [Forta Network](https://forta.org/)
- [Trail of Bits - Smart Contract Security](https://github.com/trailofbits/publications)
- [ConsenSys Smart Contract Best Practices](https://consensys.github.io/smart-contract-best-practices/)
- [Immunefi Bug Bounty Platform](https://immunefi.com/)