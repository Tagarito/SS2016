package ssof.group23;

/**
 * Created by miguel on 11/23/16.
 */

import org.antlr.v4.runtime.ParserRuleContext;
import org.tempura.console.util.Ansi;

import java.io.File;
import java.io.IOException;

public class Application {
    public static void main(String[] args) throws IOException {
        boolean printAST = true; //For debug purpuses

        if(args.length == 0) {
            System.out.println(Ansi.HIGH_INTENSITY + Ansi.RED + "Usage ./gradlew [build] run -Dexec.args=\"sliceSourcePath\"" + Ansi.LOW_INTENSITY + Ansi.BLACK);
            System.exit(1);
        }

        //Getting file
        File file = new File(args[0]);

        //Creating ast
        ParserFacade parserFacade = new ParserFacade();
        ParserRuleContext ast = parserFacade.parse(file);

        if(printAST) {
            AstPrinter astPrinter = new AstPrinter();
            astPrinter.print(ast);
        }

        //Now the juicy part
        VulnerabilityDetector detector = new VulnerabilityDetector(ast);
        detector.detect(); //Do your thing
    }
}
