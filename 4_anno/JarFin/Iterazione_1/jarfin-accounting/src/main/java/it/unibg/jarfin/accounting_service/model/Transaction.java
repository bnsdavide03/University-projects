package it.unibg.jarfin.accounting_service.model;

import jakarta.persistence.*;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;
import java.math.BigDecimal;
import java.time.LocalDate;
import java.util.Objects;

@Entity
@Table(name = "transactions")
@Getter
@Setter
@NoArgsConstructor
public class Transaction {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @Column(nullable = false, precision = 19, scale = 4)
    private BigDecimal amount;

    @Column(nullable = false)
    private LocalDate date;

    private String description;

    @Column(nullable = false)
    private String category;

    @Enumerated(EnumType.STRING)
    @Column(nullable = false)
    private TransactionType type;

    /**
     * Indicates whether some other object is "equal to" this one.
     * Two transactions are considered equal if and only if they have the same
     * identifier.
     * 
     * @param o the reference object with which to compare.
     * @return {@code true} if this object is the same as the object argument;
     *         {@code false} otherwise.
     */
    @Override
    public boolean equals(Object o) {
        if (this == o)
            return true;
        if (o == null || getClass() != o.getClass())
            return false;
        Transaction that = (Transaction) o;
        return id != null && Objects.equals(id, that.id);
    }

    /**
     * This implementation returns the hash code of the class, which is the same for
     * all instances.
     * This is because the equality of two transactions is determined solely by
     * their identifiers,
     * and the hash code of an object should be equal to the hash code of another
     * object
     * if and only if the two objects are equal according to the
     * {@link #equals(Object)} method.
     * 
     * @return the hash code of the object
     */
    @Override
    public int hashCode() {
        return getClass().hashCode();
    }
}